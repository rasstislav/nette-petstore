<?php

declare(strict_types=1);

namespace App\Utils;

class FileInfo
{
	public static function getSuggestedExtensionFromString(string $imageData): ?string
	{
		$exts = finfo_buffer(finfo_open(FILEINFO_EXTENSION), $imageData);

		if ($exts && $exts !== '???') {
			return preg_replace('~[/,].*~', '', $exts);
		}

		[, , $type] = getimagesizefromstring($imageData);

		if ($type) {
			return image_type_to_extension($type, false);
		}

		return null;
	}
}
