<?php

declare(strict_types=1);

namespace Chvdy\RedisModel\Enums;

class ExceptionCodes
{
    public static int $Validation = 1001;
    public static int $NoChangesDetected = 1002;
    public static int $ParentObjectNotFound = 1004;
    public static int $RelatedObjectNotFound = 1005;
    public static int $AlreadyRelated = 1006;
    public static int $CannotSaveRelation = 1007;
    public static int $HasNotRelated = 1008;
    public static int $CannotRemoveFromSet = 1009;
    public static int $NotFound = 1010;
    public static int $WrongIncrementId = 1011;
    public static int $CannotSaveHashCollectionFields = 1012;
    public static int $CannotRemove = 1013;
    public static int $ConnectionFailed = 1014;
    public static int $CannotGetIds = 1015;
    public static int $GetRelatedParentObjectNotFound = 1016;
    public static int $CannotGetSetMembers = 1017;
}
