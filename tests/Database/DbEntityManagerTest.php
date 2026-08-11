<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Database;

use DateTimeImmutable;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\Database\Exceptions\InvalidDbDataException;
use LM\WebFramework\Database\Exceptions\NullDbDataNotAllowedException;
use LM\WebFramework\DataStructures\Factory\CollectionFactory;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\Model\Type\StringModel;
use PHPUnit\Framework\TestCase;

final class DbEntityManagerTest extends TestCase
{
    private DbEntityManager $em;

    public function setUp(): void
    {
        $this->em = new DbEntityManager();
    }

    public function testScalarTypes(): void
    {
        $this->assertTrue($this->em->convertDbScalar(1, new BoolModel()));
        $this->assertFalse($this->em->convertDbScalar(0, new BoolModel()));
        $this->assertNull($this->em->convertDbScalar(null, new BoolModel(isNullable: true)));

        $this->assertEquals(47, $this->em->convertDbScalar(47, new IntModel()));

        $date = '2024-07-13';
        $this->assertEquals(new DateTimeImmutable($date), $this->em->convertDbScalar($date, new DateTimeModel()));
    }

    public function testConversionToDbValue(): void
    {
        $appObject = CollectionFactory::createDeepAppObject([
            'id' => 0,
            'name' => 'Georges',
        ]);
        $expected = [
            'name' => 'Georges',
        ];
        $this->assertEquals($expected, $this->em->toDbValue($appObject, ignoreProperties: ['id']));
    }

    public function testInvalidDbDataException(): void
    {
        $this->expectException(InvalidDbDataException::class);
        $this->em->convertDbScalar(true, new IntModel());
    }

    public function testNullDbDataNotAllowedException(): void
    {
        $this->expectException(NullDbDataNotAllowedException::class);
        $this->em->convertDbScalar(null, new BoolModel());
    }

    public function testConversionToEntity(): void
    {
        $dbRows = [
            [
                'entity_id' => 'test',
                'entity_child_id' => 'prout',
                'child_id' => 'prout',
                'child_name' => 'Annyong',
                'child_real_name' => 'Hel-Loh',
                'child_age' => '43',
            ],
        ];

        $expectedAppObject = (new CollectionFactory())->createDeepAppObject([
            'id' => 'test',
            'child_id' => 'prout',
            'child' => [
                'id' => 'prout',
                'name' => 'Annyong',
                'real_name' => 'Hel-Loh',
                'age' => 43,
            ],
        ]);

        $entity = new EntityModel(
            'entity',
            [
                'id' => new StringModel(),
                'child_id' => new StringModel(),
                'child' => new EntityModel(
                    'child',
                    [
                        'id' => new StringModel(),
                        'name' => new StringModel(),
                        'real_name' => new StringModel(),
                        'age' => new IntModel(),
                    ],
                )
            ],
        );

        $this->assertEquals(
            $expectedAppObject,
            $this->em->convertDbRowsToAppObject($dbRows, $entity),
        );
    }

    public function testForeignEntity(): void
    {
        $dbRows = [
            [
                'category_id' => 'all-articles',
                'category_parent_id' => null,
            ],
            [
                'category_id' => 'some-specific-articles',
                'category_parent_id' => 'all-articles',
            ],
            [
                'category_id' => 'some-even-more-specific-articles',
                'category_parent_id' => 'all-articles',
            ],
            [
                'category_id' => 'all-reviews',
                'category_parent_id' => null,
            ],
        ];

        $expectedAppObject = (new CollectionFactory())->createDeepAppObject([
            'id' => 'some-specific-articles',
            'parent' => [
                'id' => 'all-articles',
                // 'parent' => null, // @todo Would be great to have a way to have itself as a foreign entity.
            ],
        ]);

        $model = new EntityModel(
            'category',
            [
                'id' => new StringModel(),
                'parent' => new ForeignEntityModel(
                    new EntityModel(
                        'category',
                        [
                            'id' => new StringModel(),
                        ],
                    ),
                    'id',
                    'parent_id',
                ),
            ],
        );

        $this->assertEquals(
            $expectedAppObject,
            $this->em->convertDbRowsToAppObject($dbRows, $model, 1),
        );

        $parentModel = new EntityModel(
            'category',
            [
                'id' => new StringModel(),
                'children' => new EntityListModel(
                    new ForeignEntityModel(
                        new EntityModel(
                            'category',
                            [
                                'id' => new StringModel(),
                                'parent_id' => new StringModel(),
                            ],
                        ),
                        'parent_id',
                        'id',
                    )
                ),
            ],
        );

        $expectedParent = (new CollectionFactory())->createDeepAppObject([
            'id' => 'all-articles',
            'children' => [
                [
                    'id' => 'some-specific-articles',
                    'parent_id' => 'all-articles',
                ],
                [
                    'id' => 'some-even-more-specific-articles',
                    'parent_id' => 'all-articles',
                ],
            ],
        ]);

        $this->assertEquals(
            $expectedParent,
            $this->em->convertDbRowsToAppObject($dbRows, $parentModel, 0),
        );
    }

    public function testEntityListModel(): void
    {
        $dbRows = [
            '4',
            '7',
            '8',
        ];
        $expectedList = [
            4,
            7,
            8,
        ];
        $model = new ListModel(new IntModel(0, 10));
        $appData = $this->em->convertDbList($dbRows, $model);
        $this->assertEquals($appData, $expectedList);
    }

    public function testSelfReferencingEntity(): void
    {
        $dbRows = [
            [
                'entity_id' => '1',
                'entity_parent_id' => '2',
            ],
            [
                'entity_id' => '2',
                'entity_parent_id' => '3',
            ],
            [
                'entity_id' => '3',
                'entity_parent_id' => null,
            ],
        ];
        $model = (new EntityModel(
            'entity',
            [
                'id' => new StringModel(),
                'parent_id' => new StringModel(isNullable: true),
            ],
        ))->addItselfAsProperty('parent', 'id', 'parent_id', true);
        $expected = (new CollectionFactory())->createDeepAppObject([
            'id' => '1',
            'parent_id' => '2',
            'parent' => [
                'id' => '2',
                'parent_id' => '3',
                'parent' => [
                    'id' => '3',
                    'parent_id' => null,
                    'parent' => null,
                ],
            ],
        ]);
        $actual = $this->em->convertDbRowsToAppObject($dbRows, $model);
        $this->assertEquals($expected, $actual);
    }

    public function testNullSubEntity(): void
    {
        $dbRows = [
            [
                'entity_id' => '1',
                'entity_parent_id' => null,
                'parent_id' => null,
                'parent_parent_id' => null,
            ],
        ];
        $model = new EntityModel(
            'entity',
            [
                'id' => new StringModel(),
                'parent_id' => new StringModel(isNullable: true),
                'parent' => new ForeignEntityModel(
                    new EntityModel(
                        'parent',
                        [
                            'id' => new IntModel(),
                            'parent_id' => new StringModel(),
                        ],
                    ),
                    'id',
                    'parent_id',
                    isNullable: true,
                ),
            ],
        );
        $expected = (new CollectionFactory())->createDeepAppObject([
            'id' => '1',
            'parent_id' => null,
            'parent' => null,
        ]);
        $actual = $this->em->convertDbRowsToAppObject($dbRows, $model);
        $this->assertEquals($expected, $actual);
    }

    public function testOuterJoin(): void
    {
        $dbRowsLeft = [
            [
                'person_id' => 0,
                'person_name' => 'Martin',
            ],
            [
                'person_id' => 1,
                'person_name' => 'Robert',
            ],
        ];
        $dbRowsRight = [
            [
                'school_id' => 1,
                'school_name' => 'UOD',
            ],
            [
                'school_id' => 2,
                'school_name' => 'GCU',
            ],
            [
                'school_id' => 3,
                'school_name' => 'HWU',
            ],
        ];
        $expected = [
            [
                'person_id' => 0,
                'person_name' => 'Martin',
                'school_id' => 1,
                'school_name' => 'UOD',
            ],
            [
                'person_id' => 1,
                'person_name' => 'Robert',
                'school_id' => 2,
                'school_name' => 'GCU',
            ],
            [
                'person_id' => null,
                'person_name' => null,
                'school_id' => 3,
                'school_name' => 'HWU',
            ],
        ];

        $this->assertSame($expected, $this->em->outerJoinDbRows($dbRowsLeft, $dbRowsRight));
    }

    public function testConversionToList(): void
    {
        $articleModel = new EntityModel(
            'article',
            [
                'id' => new StringModel(),
                'author_id' => new IntModel(),
            ],
        );
        $personModel = new EntityModel(
            'person',
            [
                'id' => new IntModel(),
                'name' => new StringModel(),
                'articles' => new EntityListModel(
                    new ForeignEntityModel($articleModel, 'author_id', 'id'),
                ),
            ],
        );
        $dbRows = [
            [
                'person_id' => 0,
                'person_name' => 'Martin',
                'article_id' => 'un-article',
                'article_author_id' => 0,
            ],
            [
                'person_id' => 0,
                'person_name' => 'Martin',
                'article_id' => 'un-autre-article',
                'article_author_id' => 1,
            ],
            [
                'person_id' => 0,
                'person_name' => 'Martin',
                'article_id' => 'encore-un-article',
                'article_author_id' => 0,
            ],
            [
                'person_id' => 2,
                'person_name' => 'George',
                'article_id' => 'yet-another-article',
                'article_author_id' => 2,
            ],
        ];
        $expected = CollectionFactory::createDeepAppList([
            [
                'id' => 0,
                'name' => 'Martin',
                'articles' => [
                    [
                        'id' => 'un-article',
                        'author_id' => 0,
                    ],
                    [
                        'id' => 'encore-un-article',
                        'author_id' => 0,
                    ],
                ],
            ],
            [
                'id' => 2,
                'name' => 'George',
                'articles' => [
                    [
                        'id' => 'yet-another-article',
                        'author_id' => 2,
                    ],
                ],
            ],
        ]);

        $this->assertEquals($expected, $this->em->convertDbRowsToList($dbRows, $personModel));
    }

    public function testPruning(): void
    {
        $model = (new EntityModel(
            'my_model',
            [
                'id' => new StringModel(),
                'sub_entity_id' => new StringModel(isNullable: true),
            ],
        ))->addItselfAsProperty('sub_entity', 'id', 'sub_entity_id', true);

        $appObject = CollectionFactory::createDeepAppObject([
            'id' => 'entity-00',
            'sub_entity_id' => 'entity-01',
            'sub_entity' => [
                'id' => 'entity-01',
                'sub_entity_id' => null,
                'sub_entity' => null,
                'extra' => 333,
            ],
            'extra' => false,
        ]);
        $expected = CollectionFactory::createDeepAppObject([
            'id' => 'entity-00',
            'sub_entity_id' => 'entity-01',
            'sub_entity' => [
                'id' => 'entity-01',
                'sub_entity_id' => null,
                'sub_entity' => null,
            ],
        ]);
        $this->assertEquals($expected, $this->em->pruneAppObject($appObject, $model));
    }
}
