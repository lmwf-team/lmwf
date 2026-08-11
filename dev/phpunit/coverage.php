#!/usr/bin/env php
<?php

declare(strict_types=1);

$lmwfDirPath = __DIR__ . '/../..';

$xmlReportPath = "$lmwfDirPath/xml-coverage/index.xml";
$reader = new SimpleXMLElement($xmlReportPath, dataIsURL: true);
$coveragePercentage = $reader->project->directory->totals->lines['percent'];

$colorStr = match(true) {
    $coveragePercentage > 90 => 'green',
    $coveragePercentage > 70 => 'orange',
    default => 'grey',
};

file_put_contents("$lmwfDirPath/docs/coverage.json", json_encode([
    'schemaVersion' => 1,
    'label' => 'Coverage',
    'message' => "$coveragePercentage%",
    'color' => $colorStr,
], flags: JSON_THROW_ON_ERROR));