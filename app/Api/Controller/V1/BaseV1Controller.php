<?php

declare(strict_types=1);

namespace App\Api\Controller\V1;

use Apitte\Core\Annotation\Controller as Apitte;
use App\Api\Controller\BaseController;

#[Apitte\Path('/v1')]
#[Apitte\Id('v1')]
abstract class BaseV1Controller extends BaseController
{
}
