<?php

namespace TravelBlog;

use Solsken\Request;

class Video {
    protected $_video;
    protected $_filetype;
    protected $_stream;
    protected $_start = -1;
    protected $_end   = -1;
    protected $_size  = 0;
    protected $_buffer = 102400;

    public function stream($filename) {
        $this->_video = $filename;

        if (!($this->_stream = fopen($filename, 'rb'))) {
            throw new Exception('Could not open stream for reading');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $this->_filetype = finfo_file($finfo, $filename);

        $this->_setHeader();

        $i = $this->_start;
        set_time_limit(0);

        while(!feof($this->_stream) && $i <= $this->_end) {
            $bytesToRead = $this->_buffer;

            if(($i + $bytesToRead) > $this->_end) {
                $bytesToRead = $this->_end - $i + 1;
            }

            $data = fread($this->_stream, $bytesToRead);

            echo $data;
            flush();
            $i += $bytesToRead;
        }

        fclose($this->_stream);
    }

    protected function _setHeader() {
        $headers = Request::getInstance()->get('headers');
        $this->_start = 0;
        $this->_size  = filesize($this->_video);
        $this->_end   = $this->_size - 1;
        header("Content-Type: " . $this->_filetype);
        header("Accept-Ranges: bytes 0-" . $this->_end);

        if (isset($headers['HTTP_RANGE'])) {
            $start = $this->_start;
            $end   = $this->_end;

            list(, $range) = explode('=', $headers['HTTP_RANGE'], 2);

            if (strpos($range, ',') !== false) {
                http_response_code(416);
                header("Content-Range: bytes {$this->_start}-{$this->_end}/{$this->_size}");
                header('X-Error: $range false');
                throw new Exception('Range not satisfiable');
            }

            if ($range == '-') {
                $start = $this->_size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $start = $range[0];

                $end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $end;
            }

            $end = ($end > $this->_end) ? $this->_end : $end;

            if ($start > $end || $start > $this->_size - 1 || $end >= $this->_size) {
                http_response_code(416);
                header("Content-Range: bytes {$this->_start}-{$this->_end}/{$this->_size}");
                header('X-Error: Second check false');
                throw new Exception('Range not satisfiable');
            }

            $this->_start = $start;
            $this->_end   = $end;
            $length       = $this->_end - $this->_start + 1;
            fseek($this->_stream, $this->_start);
            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: " . $length);
            header("Content-Range: bytes {$this->_start}-{$this->_end}/{$this->_size}");
        } else {
            header("Content-Length: " . $this->_size);
        }
    }
}
