<?php

declare(strict_types=1);

namespace App\Api\Controller;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\UI\Controller\IController;

#[Apitte\Path('/api')]
#[Apitte\Id('api')]
abstract class BaseController implements IController
{
}
