<?php

declare(strict_types=1);

return [
    // RelationManager exceptions
    'ParentObjectNotFound' => 'Parent object not found #{0} in relation {1}',
    'CannotGetSetMembers' => 'Cannot get members of SET',
    'RelatedObjectNotFound' => '#{0} Related object not found: {1} #{2}',
    'AlreadyRelated' => '#{0} Already related. Relation:{1}',
    'CannotSaveRelation' => '#{0} Cannot save relation.',
    'HasNotRelated' => '#{0} Has not related {1} #{2}',
    'CannotRemoveFromSet' => '#{0} An error occurred while deleting relation {1}',
    
    // Repository exceptions
    'NotFound' => '#{0} Entity not found.',
    'WrongIncrementId' => 'Wrong increment id.',
    'CannotSaveHashCollectionFields' => '#{0} Cannot save hash collection.',
    'CannotRemove' => '#{0} Cannot be deleted.',
    
    // Model exceptions
    'Validation' => ' Invalid parameters.',
    'NoChangesDetected' => ' #{0} No different are detected',
    
    // Client exceptions
    'ConnectionFailed' => 'Redis connection failed and was suspended after {0} retries: {1}',
    
    // KeyManager exceptions
    'CannotGetIds' => 'Cannot get ids.',
];
