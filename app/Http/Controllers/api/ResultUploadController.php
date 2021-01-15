<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Vision\VisionClient;
use Storage;

class ResultUploadController extends Controller
{
    //
    public function postUpload(Request $request)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            //dd($base64image);

            $extension = $request->image->extension();

            $imageName = md5(time()).'.'.$extension;
            $request->image->move(getcwd()."/uploads", $imageName);

            $file_path = getcwd(). "/uploads/".$imageName;
            $base64image = file_get_contents($file_path);

            $vision = new VisionClient(['keyFile' => json_decode(file_get_contents(getcwd(). "/../key.json"), true)]);
            $image = $vision->image(
                $base64image,
                [
                    'TEXT_DETECTION',
                    //'LABEL_DETECTION',
                    //'LOGO_DETECTION'
                ]
            );

            $result = $vision->annotate($image);
            //dd($result);

            $texts = $result->text();
            $distance = 0;

            foreach ($texts as $key=>$text) {
                $description[]=$text->description();
                $textDescription = $text->description();
                preg_match("/^[0-9]*\.[0-9]+/", $textDescription, $matches);
                if (isset($matches[0])) {
                    $distance = (float)$textDescription;
                    break;
                }
            }

            //dd($description);

            return response()->json([
                'image_url'=>url("/uploads/".$imageName),
                'distance'=>$distance
            ]);

            //print best match//

        //$best_match = current($match_condition);
        }
    }
}
