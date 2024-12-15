<?php

declare(strict_types=1);

namespace App\Storage;

use App\Codec\DataCodec;
use Nette\Utils\FileSystem;

class FileStorage implements Storage
{
	public function __construct(
		private string $filePath,
		private string $entityClass,
		private string $entityCollectionClass,
		private DataCodec $codec,
	) {
	}

	public function load(): array
	{
		$data = FileSystem::read($this->filePath);

		return $this->codec->decode($data, $this->entityCollectionClass)->getItems();
	}

	public function save(array $data): void
	{
		$result = $this->codec->encode($data, $this->entityClass);

		FileSystem::write($this->filePath, $result);
	}
}
