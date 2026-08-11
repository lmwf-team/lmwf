<?php

declare(strict_types=1);

namespace LM\WebFramework\Tests\Database;

use DateTimeImmutable;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\Database\Exceptions\InvalidDbDataException;
use LM\WebFramework\Database\Exceptions\NullDbDataNotAllowedException;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\Model\Type\StringModel;
use PhpParser\Node\Expr\Cast\String_;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\isNan;

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

        $expectedAppObject = new AppObject([
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

        $expectedAppObject = new AppObject([
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

        $expectedParent = new AppObject([
            'id' => 'all-articles',
            'children' => new AppObject([
                new AppObject([
                    'id' => 'some-specific-articles',
                    'parent_id' => 'all-articles',
                ]),
                new AppObject([
                    'id' => 'some-even-more-specific-articles',
                    'parent_id' => 'all-articles',
                ]),
            ]),
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
        $expected = new AppObject([
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
}