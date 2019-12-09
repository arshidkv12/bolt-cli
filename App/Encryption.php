<?php 

namespace Bolt; 

class Encryption{

    protected $printer;
    
    public function __construct(){
        $this->printer = new CliPrinter();
    }

    public function encrypt($src, $php_blot_key, $output){
        $excludes = array();

        if (!extension_loaded('bolt')) {
            $this->printer->display( 'Please install bolt.so https://phpBolt.com' );
            $this->printer->display( 'PHP Version '. phpversion() );
            $this->printer->display( 'INI file location '.php_ini_scanned_files() );
            $this->printer->display( 'Extension dir: '.ini_get('extension_dir') );
            return;
        }
        foreach($excludes as $key => $file){
            $excludes[ $key ] = $src.'/'.$file; 
        }
        $rec = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator( $src ));
        $require_funcs = array('include_once', 'include', 'require', 'require_once'); 
        foreach ($rec as $file) {
            if ($file->isDir()) {
                $newDir  = str_replace( $src, $output, $file->getPath() );
                if( !is_dir( $newDir ) ) mkdir( $newDir );
                continue;
            };
            $filePath = $file->getPathname();
            if( pathinfo($filePath, PATHINFO_EXTENSION) != 'php'  ||
                in_array( $filePath, $excludes ) ) {  
                $newFile  = str_replace($src, $output, $filePath );
                copy( $filePath, $newFile );
                continue;
            }
            $contents = file_get_contents( $filePath );  
            $cipher   = \bolt_encrypt( "?>".$contents, $php_blot_key );
            $preppand = '<?php bolt_decrypt( __FILE__ , PHP_BOLT_KEY); return 0;
            ##!!!##';
            $re = '/\<\?php/m';
            preg_match($re, $contents, $matches ); 
            
            if( !empty($matches[0]) ) $contents = preg_replace( $re, '', $contents );
            
            $newFile  = str_replace($src, $output, $filePath );
            $fp       = fopen( $newFile, 'w');
            fwrite($fp, $preppand.$cipher);
            fclose($fp);
            unset( $cipher );
            unset( $contents );
        }
        $out_str       = substr_replace($src, '', 0, 4);
        $file_location = __DIR__."/encrypted".$out_str;
        $this->printer->display( "Successfully Encrypted... Please check in " .$file_location." folder.");
        
    }

}
