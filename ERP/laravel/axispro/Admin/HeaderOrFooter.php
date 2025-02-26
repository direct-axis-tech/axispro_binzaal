<?php

namespace Axispro\Admin;

use Illuminate\Support\Facades\Storage;

class HeaderOrFooter {
	/**
	 * Upload the file if possible
	 *
	 * @param string $fileKey
	 * @param string $id
	 * @return void
	 */
	public static function upload($fileKey, $id=null) {
		if (request()->hasFile($fileKey) && ($uploadedFile = request()->file($fileKey))->isValid()) {
			$fileName = self::fileName($fileKey, $id);

			// Delete previously uploaded file
			self::delete($fileName);

			$uploadedFile->storeAs(self::uploadLocation(), "{$fileName}.".$uploadedFile->extension(), ['disk' => 'public']);
		}
	}

	/**
	 * Deletes the existing uploaded file
	 *
	 * @param string $fileNameWithoutExtension
	 * @return void
	 */
	public static function delete($fileNameWithoutExtension)
	{
		if ($existingFile = self::existingFile($fileNameWithoutExtension)) {
			Storage::disk('public')->delete($existingFile);
		}
	}

	/**
	 * Returns the existing uploaded file path
	 *
	 * @param string $key The key that is prepended
	 * @param string $id The id of the dimension if any
	 * @return string|null
	 */
	public static function existingFile($key, $id=null)
	{
		$fileNameWithoutExtension = self::fileName($key, $id);
		foreach (Storage::disk('public')->allFiles(self::uploadLocation()) as $file) {
			if (pathinfo($file, PATHINFO_FILENAME) == $fileNameWithoutExtension) {
				return $file;
			}
		}

		return null;
	}

	/**
	 * Returns the location of the uploaded folder
	 *
	 * @return void
	 */
	public static function uploadLocation()
	{
		return "headers-and-footers";
	}

	/**
	 * Returns the uploaded file name
	 *
	 * @param string $key The key that is prepended
	 * @param string $id The id of the dimension if any
	 * @return string
	 */
	public static function fileName($key, $id=null)
	{
		$fileName = "{$key}";

		if ($id) {
			$fileName .= "_{$id}";
		}

		return $fileName;
	}

	/**
	 * Handle the file operations such as view and delete
	 *
	 * @return void
	 */
	public static function handleFileOperation($metaData)
	{
		global $Ajax;
		
		foreach ($metaData as $key => $data) {
			// Handle view request
			$id = find_submit($data['viewBtn'], false);
			$fileName = self::fileName($key, $id);

			if (!is_null($id) && in_ajax() && ($filePath = self::existingFile($fileName))) {
				$Ajax->popup(Storage::disk('public')->url($filePath));
			}

			// Handle delete request
			$id = find_submit($data['deleteBtn'], false);
			$fileName = self::fileName($key, $id);
			if (!is_null($id) && in_ajax() && ($filePath = self::existingFile($fileName))) {
				Storage::disk('public')->delete($filePath);
				display_notification('The selected file has been deleted successfully');
				$Ajax->activate("_$key");
			}
		}
	}

	/**
	 * Return the full path of the file
	 *
	 * @param string $filePath
	 * @return void
	 */
	public static function path($filePath)
	{
		return Storage::disk('public')->path($filePath);
	}
}