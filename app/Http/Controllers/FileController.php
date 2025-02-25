<?php

namespace App\Http\Controllers;
use Illuminate\Http\UploadedFile;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    static $default = 'defaultUser.png';
    static $diskName = 'ImageStorage';

    static $systemTypes = [
        'accountAsset' => ['png'],
    ];

    private static function isValidType(String $type) {
        return array_key_exists($type, self::$systemTypes);
    }

    private static function defaultAsset(String $type) {
        return asset($type . '/' . self::$default);
    }

    private static function getFileName (String $type, int $id) {

        $fileName = null;
        if ($type == 'accountAsset') {
            $account = Account::find($id);
            $fileName = $account->accountImage?->image; // Use the relationship without parentheses
        }
        return $fileName;
    }

    static function get(String $type, int $accountId) {
        // Validation: upload type
        if (!self::isValidType($type)) {
            return self::defaultAsset($type);
        }
        // Validation: file exists
        $fileName = self::getFileName($type, $accountId);
        if ($fileName) {
            return asset($type . '/' . $fileName);
        }

        // Not found: returns default asset
        return self::defaultAsset($type);
    }

    public static function upload(UploadedFile $file, string $type, int $id){

        $extension = $file->getClientOriginalExtension();

        // Hashing
        $fileName = $file->hashName(); // generate a random unique id

        // Save in correct folder and disk
        $file->storeAs($type, $fileName, self::$diskName);
    }




}
