<?php 
    /**
     * Lily - Error / exception class
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily;

    class Error extends \Exception {
        protected $message;
        protected $code;
        protected $data;

        public function __construct(string $message, string $code = null, $data = []) {
            $this->message = $message;
            $this->code = $code;
            $this->data = $data;
        }
    }