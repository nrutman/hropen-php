<?php

require_once __DIR__ . '/vendor/autoload.php';

$swaggerSchema = \Swaggest\JsonSchema\Schema::import('http://127.0.0.1:8080/assessments/json/AssessmentType.json');

$appPath = realpath(__DIR__ . '/output');
$appNs = 'HrOpen';

$app = new \Swaggest\PhpCodeBuilder\App\PhpApp();
$app->setNamespaceRoot($appNs, '.');

$builder = new \Swaggest\PhpCodeBuilder\JsonSchema\PhpBuilder();
$builder->buildSetters = true;
$builder->makeEnumConstants = true;

$builder->classCreatedHook = new \Swaggest\PhpCodeBuilder\JsonSchema\ClassHookCallback(
    function (\Swaggest\PhpCodeBuilder\PhpClass $class, $path, $schema) use ($app, $appNs) {
        $desc = '';

        echo "Schmea: $schema->title\n";

        if ($schema->title) {
            $desc = $schema->title;
        }
        if ($schema->description) {
            $desc .= "\n" . $schema->description;
        }
        if ($fromRefs = $schema->getFromRefs()) {
            $desc .= "\nBuilt from " . implode("\n" . ' <- ', $fromRefs);
        }

        $class->setDescription(trim($desc));

        $class->setNamespace($appNs);
        if ('#' === $path) {
            $class->setName('User'); // Class name for root schema
        } elseif (strpos($path, '#/definitions/') === 0) {
            $class->setName(\Swaggest\PhpCodeBuilder\PhpCode::makePhpClassName(
                substr($path, strlen('#/definitions/'))));
        }
        $app->addClass($class);
    }
);

$builder->getType($swaggerSchema);
$app->clearOldFiles($appPath);
$app->store($appPath);