<?php
class DB {
    private $connect;
    private static $credentials = null;

    public function __construct(string $host, string $database, string $username, string $password, int $port = 3306) {
        self::$credentials = [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password
        ];
        $this->connect();
    }

    private function connect() {
        $this->connect = new PDO(
            'mysql:host=' . DB::$credentials['host'] .
            ';dbname=' . DB::$credentials['database'] .
            ';port=' . DB::$credentials['port'],
            DB::$credentials['username'],
            DB::$credentials['password']
        );
        $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connect->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
        $this->connect->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    public function rawQuery(string $query) {
        if (DB::$credentials != null) {
            $query = $this->connect->query($query);
            return $query;
        } else {
            throw new Exception("Not connected to database server");
            return false;
        }
    }

    public function preparedQuery(string $query, array $parameters) {
        if (DB::$credentials != null) {
            $query = $this->connect->prepare($query);
            $query->execute($parameters);
            return $query;
        } else {
            throw new Exception("Not connected to database server");
            return false;
        }
    }
}

class Upload {
    protected static string $directory;
    public static array $file;
    protected static string $type;

    private static $ffmpeg;
    private static $ffprobe;
    private static $ffmpeg_params;

    public function __construct(string $directory) {
        self::$directory = (realpath($directory) ? realpath($directory) : realpath('/')) . "\\";
    }

    private function prepareImage($file) {
        $info = getimagesize($file);
        switch ($info["mime"]) {
            case "image/jpeg":
                return imagecreatefromjpeg($file);
            case "image/gif":
                return imagecreatefromgif($file);
            case "image/png":
                return imagecreatefrompng($file);
            case "image/bmp":
                return imagecreatefrombmp($file);
            case "image/webp":
                return imagecreatefromwebp($file);
            case "image/avif":
                return imagecreatefromavif($file);
            default:
                throw new Exception("Invalid image mime type", 1);
                return false;
        }
    }

    public static function file(array $file) {
        self::$file["name"] = sha1(openssl_random_pseudo_bytes(32));
        self::$file["type"] = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        self::$type = explode('/', $file["type"])[0];
        $file["name"] = self::$file["name"] . '.' . self::$file["type"];
        var_dump($file);
        self::$file["directory"] = self::$directory . basename($file["name"]);
        if (move_uploaded_file($file["tmp_name"], self::$file["directory"])) {
            if (self::$type != "image") {
                self::$ffmpeg_params = [
                    "ffmpeg.binaries" => CONFIG["ffmpeg"]["ffmpeg"],
                    "ffprobe.binaries" => CONFIG["ffmpeg"]["ffprobe"],
                    "timeout" => CONFIG["ffmpeg"]["timeout"],
                    "ffmpeg.threads" => CONFIG["ffmpeg"]["threads"],
                    "temporary_directory" => self::$directory
                ];
                self::$ffmpeg = FFMpeg\FFMpeg::create(self::$ffmpeg_params);
                self::$ffprobe = FFMpeg\FFProbe::create(self::$ffmpeg_params);
            }
            return new self(self::$directory);
        } else {
            throw new Exception("Error uploading file", 1);
            return false;
        }
    }

    public function thumbnail(int $time = -1) {
        if (self::$type == "video") {
            $video = self::$ffmpeg->open(self::$file["directory"]);
            $format = self::$ffprobe->format(self::$file["directory"]);
            $thumb = $time == -1 ? ceil($format->get("duration") / 2) + 1 : abs($time);
            $thumb = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($thumb));
            $thumb->save(self::$directory . substr(self::$file["name"], 1) . "vt.jpg");
        } else {
            throw new Exception("Specified file is not a supported format", 3);
            return false;
        }
        return $this;
    }

    public function compress(int $percentage = 50) {
        switch (self::$type) {
            case "image":
                $file = self::prepareImage(self::$file["directory"]);
                $extension = getimagesize(self::$file["directory"])["mime"];
                switch ($extension) {
                    case "image/gif":
                        //imagetruecolortopalette($file, true, round(256 * $percentage / 100));
                        //imagegif($file, self::$file["directory"]);
                        break;
                    default:
                        imagejpeg($file, self::$file["directory"], $percentage);
                        break;
                }
                imagedestroy($file);
                break;
            case "video":
                $video = self::$ffmpeg->open(self::$file["directory"]);
                $format = self::$ffprobe->format(self::$file["directory"]);
                $bitrate = round($format->get("bit_rate") * ($percentage / 100) / 1000);
                $format = new FFMpeg\Format\Video\X264();
                $format->setKiloBitrate($bitrate)->setAudioChannels(2)->setAudioKiloBitrate(128);
                $video->save($format, self::$directory . substr(self::$file["name"], 1) . "v.mp4");
                unlink(self::$file["directory"]);
                self::$file["directory"] = self::$directory . substr(self::$file["name"], 1) . "v.mp4";
                break;
            case "audio":
                $audio = self::$ffmpeg->open(self::$file["directory"]);
                $format = self::$ffprobe->format(self::$file["directory"]);
                $bitrate = round($format->get("bit_rate") * ($percentage / 100));
                $format = new FFMpeg\Format\Audio\Mp3();
                $format->setAudioChannels(2)->setAudioKiloBitrate($bitrate);
                $audio->save($format, self::$directory . substr(self::$file["name"], 1) . "a.mp3");
                unlink(self::$file["directory"]);
                self::$file["directory"] = self::$directory . substr(self::$file["name"], 1) . "a.mp3";
                break;
            default:
                throw new Exception("Specified file is not a supported format", 3);
                return false;
        }
        return $this;
    }

    public function rescale() {
        $parameters = func_get_args();
        if (!count($parameters) > 0 && !count($parameters) < 3) {
            throw new Exception("Invalid parameter(s) count", 4);
            return false;
        }
        switch (self::$type) {
            case "image":
                $file = self::prepareImage(self::$file["directory"]);
                if (count($parameters) === 2) {
                    $width = $parameters[0];
                    $height = $parameters[1];
                } else {
                    list($width, $height) = getimagesize(self::$file["directory"]);
                    $percentage = $parameters[0];
                    $width = round($width * ($percentage / 100));
                    $height = round($height * ($percentage / 100));
                }
                $output = imagescale($file, $width, $height, IMG_BILINEAR_FIXED);
                imagejpeg($output, self::$file["directory"]);
                imagedestroy($file);
                break;
            case "video":
                $video = self::$ffmpeg->open(self::$file["directory"]);
                $format = self::$ffprobe->format(self::$file["directory"]);
                if (count($parameters) === 2) {
                    $width = $parameters[0];
                    $height = $parameters[1];
                } else {
                    $width = $format->get("width");
                    $height = $format->get("height");
                    $percentage = $parameters[0];
                    $width = round($width * ($percentage / 100));
                    $height = round($height * ($percentage / 100));
                }
                $format = new FFMpeg\Format\Video\X264();
                $video->filters()->resize(new FFMpeg\Coordinate\Dimension($width,$height), FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_INSET);
                $video->save($format, self::$directory . substr(self::$file["name"], 1) . "v.mp4");
                unlink(self::$file["directory"]);
                self::$file["directory"] = self::$directory . substr(self::$file["name"], 1) . "v.mp4";
                break;
            default:
                throw new Exception("Specified file is not a supported format", 3);
                return false;
        }
        return $this;
    }

    public function convert(string $format) {
        switch (self::$type) {
            case "image":
                $file = self::prepareImage(self::$file["directory"]);
                $directory = self::$directory . self::$file["name"] . '.';
                switch ($format) {
                    case "image/jpeg":
                        imagejpeg($file, $directory . "jpg");
                        break;
                    case "image/gif":
                        imagegif($file, $directory . "gif");
                        break;
                    case "image/png":
                        imagepng($file, $directory . "png");
                        break;
                    case "image/bmp":
                        imagebmp($file, $directory . "bmp");
                        break;
                    case "image/webp":
                        imagewebp($file, $directory . "webp");
                        break;
                    case "image/avif":
                        imageavif($file, $directory . "avif");
                        break;
                    default:
                        throw new Exception("Specified file is not a supported format", 3);
                        return false;
                }
                unlink(self::$file["directory"]);
                break;
            default:
                throw new Exception("Specified file is not a supported format", 3);
                return false;
        }
        return $this;
    }
}