<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class ValidMessageAudience extends Constraint
{
    public $message = "Vous n'avez pas le droit d'utiliser cette audience";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
