# CodeIgniter4 Redis Model
A comprehensive Redis-based model solution for CodeIgniter 4 that provides an alternative to traditional database models. This package uses Redis Hash data structures integrated with CodeIgniter's Validation and Entity systems to deliver high-performance, scalable data persistence.

## Features
- **Redis Hash Storage**: Efficient data storage using Redis Hash data structures
- **Auto-increment IDs**: Automatic ID generation using Redis counters
- **Entity Integration**: Full compatibility with CodeIgniter's Entity system
- **Validation Support**: Built-in validation using CodeIgniter's validation service
- **Relationship Management**: Support for has-many relationships using Redis Sets
- **Field Protection**: Configurable field protection to prevent unauthorized updates
- **Translation Support**: Multi-language error message support
- **Change Detection**: Intelligent update system that only saves modified fields

## Architecture
The Redis Model solution consists of several key components:

### Core Components
- **Model**: Abstract base class that extends functionality similar to CodeIgniter's built-in models
- **Repository**: Handles CRUD operations using Redis Hash commands (hGetAll, hMSet, del)
- **Validator**: Integrates with CodeIgniter's validation system and provides field protection
- **RelationManager**: Manages relationships using Redis Sets with assign/unassign operations
- **KeyManager**: Handles Redis key generation and management using entity FQN patterns
- **Client**: Redis connection wrapper with error handling

### Data Storage Patterns
- **Entity Storage**: Hash keys using pattern `{entity.fqn}:{id}`
- **Relationships**: Set keys using pattern `{parent.entity}__{child.entity}:{parent_id}`
- **Counters**: Auto-increment keys for ID generation using entity FQN `{entity.fqn}`
- **Key Naming**: Lowercase with dots replacing namespace separators

## Installation
TBC

## Configuration
TBC

## Basic Usage

### Creating a Model

Create a model by extending `\Chvdy\RedisModel\Model`. Your entity must have an `id` attribute and should extend CodeIgniter's Entity class.

```php
<?php

namespace App\Models;

class Car extends \Chvdy\RedisModel\Model
{
    protected string $entity = \App\Entities\Car::class;

    protected array $has_many = [
        'drivers' => \App\Models\Driver::class,
    ];

    protected array $rules = [
        'name' => 'required|alpha_numeric_space', // validation rules
        'top_speed' => 'required|is_natural_no_zero',
        'fuel_capacity' => 'required|greater_than[0]',
        'first_inspection' => 'required|valid_date[Y-m-d]',
        'last_inspection' => 'valid_date[Y-m-d]',
    ];

    protected array $protectFields = ['first_inspection']; // Cannot be changed after creation

    protected array $errors = [
        'name' => [
            'required' => 'ValidationErrors.CarNameRequired', // language key
            'alpha_numeric_space' => 'ValidationErrors.CarNameAlphaNumericSpace',
        ],
        'top_speed' => [
            'required' => 'ValidationErrors.CarTopSpeedRequired',
            'is_natural_no_zero' => 'ValidationErrors.CarTopSpeedIsNaturalNoZero',
        ],
        'fuel_capacity' => [
            'required' => 'ValidationErrors.CarFuelCapacityRequired',
            'greater_than' => 'ValidationErrors.CarFuelCapacityGreaterThan',
        ],
        'first_inspection' => [
            'required' => 'ValidationErrors.CarFirstInspectionRequired',
            'valid_date' => 'ValidationErrors.CarFirstInspectionValidDate',
        ],
        'last_inspection' => [
            'valid_date' => 'ValidationErrors.CarLastInspectionValidDate',
        ],
    ];
}
```

### Creating an Entity

```php
<?php

namespace App\Entities;

class Car extends \CodeIgniter\Entity\Entity
{
    protected $attributes = [
        'id' => null,
        'name' => null,
        'top_speed' => null,
        'fuel_capacity' => null,
        'first_inspection' => null,
        'last_inspection' => null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $dates = ['created_at', 'updated_at'];
    
    protected $casts = [
        'id' => 'integer',
        'top_speed' => 'integer',
        'fuel_capacity' => 'float',
        'first_inspection' => 'date',
        'last_inspection' => 'date',
    ];

    protected $castHandlers = [
        'date' => \App\Entities\Cast\DateCast::class,
    ];
}
```

## API Reference

### Model Methods

#### `create(array $data): int`
Creates a new entity and returns the generated ID.

```php
$model = model(\App\Models\Car::class);
$id = $model->create([
    'name' => 'DeLorean DMC-12',
    'top_speed' => 1250,
    'fuel_capacity' => 100.0,
    'first_inspection' => '1985-07-03'
]);
```

**Throws:**
- `\Chvdy\RedisModel\Exceptions\Validation` - When validation fails
- `\Chvdy\RedisModel\Exceptions\WrongIncrementId` - When ID generation fails
- `\Chvdy\RedisModel\Exceptions\CannotSaveHashCollectionFields` - When Redis save operation fails
- `\Chvdy\RedisModel\Exceptions\ConnectionFailed` - When Redis connection fails

#### `get(int $id): Entity`
Retrieves an entity by ID.

```php
/** @var $car \App\Entities\Car */
$car = $model->get($id);
echo $car->name; // DeLorean DMC-12
```

**Throws:**
- `\Chvdy\RedisModel\Exceptions\NotFound` - When entity doesn't exist

#### `getAll(): array`
Retrieves all entities of this type.

```php
$cars = $model->getAll();
foreach ($cars as $car) {
    echo $car->name;
}
```

**Throws:**
- `\Chvdy\RedisModel\Exceptions\ConnectionFailed` - When Redis connection fails
- `\Chvdy\RedisModel\Exceptions\CannotGetIds` - When unable to retrieve entity IDs

#### `update(int $id, array $data, array $protectedFields = []): bool`
Updates an existing entity with change detection.

```php
$model->update($id, [
    'top_speed' => 260,
    'last_inspection' => '2025-09-08'
], ['first_inspection']); // Protect first_inspection from changes
```

**Throws:**
- `\Chvdy\RedisModel\Exceptions\NotFound` - When entity doesn't exist
- `\Chvdy\RedisModel\Exceptions\NoChangesDetected` - When no changes are detected
- `\Chvdy\RedisModel\Exceptions\Validation` - When validation fails

#### `setProtectFields(array $fields, bool $flush = false): void`
Sets fields that should be protected from updates.

```php
$model->setProtectFields(['first_inspection', 'created_at']);
$model->setProtectFields(['updated_at'], true); // Flush existing and set new
```

### Relationship Management

#### `assign(string $relation, int $id_parent, int $id_related): bool`
Creates a relationship between two entities.

```php
$model->relationManager->assign('drivers', $car_id, $driver_id);
```

**Throws:**
- `\Chvdy\RedisModel\Exceptions\ParentObjectNotFound` - When parent entity doesn't exist
- `\Chvdy\RedisModel\Exceptions\RelatedObjectNotFound` - When related entity doesn't exist
- `\Chvdy\RedisModel\Exceptions\AlreadyRelated` - When relationship already exists
- `\Chvdy\RedisModel\Exceptions\CannotSaveRelation` - When Redis save operation fails

#### `unassign(string $relation, int $id_parent, int $id_related): int`
Removes a relationship between two entities.

```php
$model->relationManager->unassign('drivers', $car_id, $driver_id);
```

**Throws:**
- `\Chvdy\RedisModel\Exceptions\HasNotRelated` - When relationship doesn't exist
- `\Chvdy\RedisModel\Exceptions\CannotRemoveFromSet` - When Redis remove operation fails

#### `getRelated(string $relation, int $id_parent): array`
Retrieves all related entities.

```php
$drivers = $model->relationManager->getRelated('drivers', $car_id);
```

**Throws:**
- `\Chvdy\RedisModel\Exceptions\ParentObjectNotFound` - When parent entity doesn't exist
- `\Chvdy\RedisModel\Exceptions\CannotGetSetMembers` - When unable to retrieve set members

#### `removeRelations(int $id): int`
Removes all relationships for an entity.

```php
$removed = $model->relationManager->removeRelations($car_id);
```

## Exception Handling

The package provides comprehensive exception handling with specific exception types:

- `\Chvdy\RedisModel\Exceptions\ConnectionFailed` - Redis connection issues
- `\Chvdy\RedisModel\Exceptions\Validation` - Validation errors with detailed field information
- `\Chvdy\RedisModel\Exceptions\NotFound` - Entity not found
- `\Chvdy\RedisModel\Exceptions\NoChangesDetected` - No changes detected during update
- `\Chvdy\RedisModel\Exceptions\WrongIncrementId` - ID generation issues
- `\Chvdy\RedisModel\Exceptions\CannotSaveHashCollectionFields` - Redis save operation failures
- `\Chvdy\RedisModel\Exceptions\AlreadyRelated` - Duplicate relationship attempts
- `\Chvdy\RedisModel\Exceptions\ParentObjectNotFound` - Parent entity missing in relationships
- `\Chvdy\RedisModel\Exceptions\RelatedObjectNotFound` - Related entity missing in relationships

## Advanced Features

### Field Protection
Protect sensitive fields from being updated after creation:
```php
protected array $protectFields = ['first_inspection'];
```

### Custom Validation Messages
Define custom validation error messages with translation support:
```php
protected array $errors = [
    'name' => [
        'required' => 'ValidationErrors.CarNameRequired',
    ],
];
```

### Automatic Timestamps
The model automatically handles `created_at` timestamps when creating entities. Update operations automatically set `updated_at` timestamps.

### Change Detection
The update method intelligently detects changes and only saves modified fields, throwing `\Chvdy\RedisModel\Exceptions\NoChangesDetected` when no changes are found.


## Database Migration
If you're migrating from traditional database models and want to disable database components:
```php
// In app/Config/Preload.php
private array $paths = [
    [
        'exclude' => [
            '/system/Database/MySQLi/',
            '/system/Database/OCI8',
            '/system/Database/SQLSRV/',
            '/system/Database/SQLite3',
            '/system/Database/Postgre',
        ],
    ]
];
```

## Requirements
- PHP 8.4+
- CodeIgniter 4.6+
- Redis Server
- PHP Redis extension
