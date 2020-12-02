<?php

/**
 * Laravel Source Encrypter.
 *
 * @author      Siavash Bamshadnia
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link        https://github.com/SiavashBamshadnia/Laravel-Source-Encrypter
 */

namespace sbamtr\LaravelSourceEncrypter;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SourceEncryptCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encrypt-source
                { --source= : Path(s) to encrypt }
                { --destination= : Destination directory }
                { --force : Force the operation to run when destination directory already exists }';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypts PHP files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!extension_loaded('showlight')) {
            $this->error('Please install showlight_enc.so');
            $this->error('PHP Version '.phpversion());
            $this->error('INI file location '.php_ini_scanned_files());
            $this->error('Extension dir: '.ini_get('extension_dir'));

            return 1;
        }

        if (empty($this->option('source'))) {
            $sources = config('source-encrypter.source', ['app', 'database', 'routes']);
        } else {
            $sources = $this->option('source');
            $sources = explode(',', $sources);
        }
        if (empty($this->option('destination'))) {
            $destination = config('source-encrypter.destination', 'encrypted');
        } else {
            $destination = $this->option('destination');
        }
         
        $keyLength = 6;

        if (!$this->option('force')
            && File::exists(base_path($destination))
            && !$this->confirm("The directory $destination already exists. Delete directory?")
        ) {
            $this->line('Command canceled.');

            return 1;
        }

        File::deleteDirectory(base_path($destination));
        File::makeDirectory(base_path($destination));

        foreach ($sources as $source) {
            @File::makeDirectory($destination.'/'.File::dirname($source), 493, true);

            if (File::isFile($source)) {
                self::encryptFile($source, $destination, $keyLength);
                continue;
            }
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($source)));
            foreach ($files as $file) {
                $filePath = Str::replaceFirst(base_path(), '', $file->getRealPath());
                self::encryptFile($filePath, $destination, $keyLength);
            }
        }
        $this->info('Encrypting Completed Successfully!');
        $this->info("Destination directory: $destination");

        return 0;
    }

    private static function encryptFile($filePath, $destination, $keyLength)
    {
        $key = Str::random($keyLength);
        if (File::isDirectory(base_path($filePath))) {
            if (!File::exists(base_path($destination.$filePath))) {
                File::makeDirectory(base_path("$destination/$filePath"), 493, true);
            }

            return;
        }

        if (File::extension($filePath) != 'php') {
            File::copy(base_path($filePath), base_path("$destination/$filePath"));

            return;
        }

        $fileContents = File::get(base_path($filePath));
        $tokens = token_get_all($fileContents);

     
        $prepend = "<?php showlight_execute( __FILE__ ); return 0;?> \n";
        $pattern = '/\<\?php/m';
        preg_match($pattern, $fileContents, $matches);
        if (!empty($matches[0])) {
            $fileContents = preg_replace($pattern, '', $fileContents);
        }
        

        $code = '';
        foreach ($tokens as $key =>  $token) {
            if (is_array($token)) {

                $match = array();
                preg_match('/[a-z]+$/', $token[1], $match );

                if( token_name($token[0]) == 'T_STRING' || token_name($token[0]) == 'T_VARIABLE' ){
                    $code .= $token[1];
                    continue;
                }

                if( empty($match[0]) ){
                    $code .=  $token[1];
                    continue;
                }
                $code .= '{'.$token[1].'}';

            }else{
                $code .= $token;
            }
        }
        // echo $code;
        /*$cipher = bolt_encrypt('?> ' . $fileContents, $key);*/
        $cipher = showlight_encrypt($code);
        File::put(base_path("$destination/$filePath"), $prepend.$cipher);

        unset($cipher);
        unset($fileContents);
    }
}
