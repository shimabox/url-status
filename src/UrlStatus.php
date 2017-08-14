<?php

namespace SMB;

/**
 * Url Status
 *
 * @author shimabox.net
 */
class UrlStatus
{
    /**
     * 対象のURL
     * @var string
     */
    private $targetUrl = '';

    /**
     * 最後に到達したURL
     * @var string
     */
    private $reachedUrl = '';

    /**
     * 対象のURLが正しいか
     * @var boolean
     */
    private $isValidUrl = false;

    /**
     * リダイレクトが行われた際のURL
     * @var array
     */
    private $redirectedUrls = [];

    /**
     * リダイレクト時のHTTPステータスコード
     * @var array
     */
    private $redirectedCode = [];

    /**
     * HTTPステータスコード
     * @var int
     */
    private $code = 0;

    /**
     * URLのステータスを返す
     *
     * <code>
     * $ret = \SMB\UrlStatus::get($url);
     *
     * $ret->targetUrl();      // string 対象のURL<br>
     * $ret->reachedUrl();     // string 最後に到達したURL<br>
     * $ret->isValidUrl();     // bool   有効なURLか<br>
     * $ret->redirectedUrls(); // array  リダイレクトがあった際のURL<br>
     * $ret->redirectedCode(); // array  リダイレクト時のHTTPステータスコード<br>
     * $ret->code();           // int    最終的なHTTPステータスコード<br>
     *
     * // is{数値3桁}() でマジックメソッドが呼ばれます<br>
     * $ret->is200();          // bool   HTTPステータスコードが200かどうか<br>
     * $ret->is401();          // bool   HTTPステータスコードが401かどうか<br>
     * $ret->is403();          // bool   HTTPステータスコードが403かどうか<br>
     * $ret->is404();          // bool   HTTPステータスコードが404かどうか<br>
     * $ret->is405();          // bool   HTTPステータスコードが405かどうか<br>
     * $ret->is500();          // bool   HTTPステータスコードが500かどうか<br>
     * $ret->is503();          // bool   HTTPステータスコードが503かどうか<br>
     * $ret->isxxx();
     * </code>
     *
     * @param string $url ステータスを見たいURL
     * @param array $streamContextOptions stream_context_set_defaultを呼びます(GETリクエスト以外とか指定したい場合など)
     * @return \SMB\UrlStatus
     * @see https://secure.php.net/manual/ja/function.get-headers.php#119497
     */
    public static function get($url, array $streamContextOptions = null)
    {
        $self = new static();
        $self->targetUrl = $url;

        if ($streamContextOptions !== null) {
            stream_context_set_default($streamContextOptions);
        }

        $headers = @get_headers($url);
        if ($headers === false) {
            return $self;
        }

        // redirectedCode と redirectedUrlsは対になるようにしたいので
        // 一旦redirectedCodeを格納するものを用意する
        $_redirectedCode = [];

        foreach($headers as $header) {
            // リダイレクトされたURL
            if (preg_match('/\ALocation:\s(http.+)\z/', $header, $m)) {
                $self->redirectedUrls[] = $m[1];
                continue;
            }
            // HTTPステータスコード
            if (preg_match('/\AHTTP.+\s(\d\d\d)\s/', $header, $m)) {
                $code = (int)$m[1];
                $_redirectedCode[] = $code;
                $self->code = $code;
                $self->isValidUrl = true;
            }
        }

        $redirectedCnt = count($self->redirectedUrls);
        if ($redirectedCnt > 0) {
            $self->reachedUrl = $self->redirectedUrls[$redirectedCnt - 1];
        } else {
            $self->reachedUrl = $url;
        }

        // redirectedCodeとredirectedUrlsの共通項を取得(redirectedCodeとredirectedUrlsは対になるように)
        $self->redirectedCode = array_intersect_key($_redirectedCode, $self->redirectedUrls);

        return $self;
    }

    /**
     * 対象のURLを返す
     * @return string
     */
    public function targetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * 最後に到達したURLを返す
     * @return string
     */
    public function reachedUrl()
    {
        return $this->reachedUrl;
    }

    /**
     * 対象のURLが正しかったか返す
     * @return boolean
     */
    public function isValidUrl()
    {
        return $this->isValidUrl;
    }

    /**
     * リダイレクトが行われた際のURLを返す
     * @return array
     */
    public function redirectedUrls()
    {
        return $this->redirectedUrls;
    }

    /**
     * リダイレクト時のHTTPステータスコードを返す
     * @return array
     */
    public function redirectedCode()
    {
        return $this->redirectedCode;
    }

    /**
     * 最終的なHTTPステータスコードを返す
     * @return int
     */
    public function code()
    {
        return $this->code;
    }

    /*
     * __call isxxx
     * @param string $name isXXX (/\Ais(\d{3})\z/)
     * @param array $args
     * @return boolean
     * @throws \LogicException
     */
    public function __call($name, $args)
    {
        if (preg_match('/\Ais(\d{3})\z/', $name, $m)) {
            $code = (int)$m[1];
            return $this->code === $code;
        }

        throw new \LogicException('Only isXXX functions are allowed');
    }

    /**
     * コンストラクタ
     */
    private function __construct() {}
}
