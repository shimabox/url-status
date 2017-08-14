<?php

namespace SMB\Tests;

use SMB\UrlStatus;

/**
 * Test Of UrlStatus
 *
 * @author shimabox.net
 */
class UrlStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * URLからヘッダー情報を取得できる
     * @test
     */
    public function it_can_get_header_information_from_url()
    {
        $url = 'https://www.google.com/webhp?gl=us&hl=en&gws_rd=cr';
        $actual = UrlStatus::get($url);

        $this->assertInstanceOf('\SMB\UrlStatus', $actual);
        $this->assertSame(200, $actual->code());
        $this->assertTrue($actual->isValidUrl());
        $this->assertSame([], $actual->redirectedCode());
        $this->assertSame([], $actual->redirectedUrls());
        $this->assertSame($url, $actual->targetUrl());
        $this->assertSame($url, $actual->reachedUrl());
        $this->assertTrue($actual->is200());
    }

    /**
     * HTTPステータスコードが404である
     * @test
     */
    public function it_is_HttpStatusCode_404()
    {
        $actual = UrlStatus::get('https://example.com/hoge.png');

        $this->assertSame(404, $actual->code());
        $this->assertTrue($actual->is404());
    }

    /**
     * URLからリダイレクト情報を取得できる
     * @test
     */
    public function it_can_get_redirected_information_from_url()
    {
        $url = 'https://google.com/webhp?gl=us&hl=en&gws_rd=cr';
        $redirectedUrl = 'https://www.google.com/webhp?gl=us&hl=en&gws_rd=cr';

        $actual = UrlStatus::get($url);

        $this->assertSame([301], $actual->redirectedCode());
        $this->assertSame([$redirectedUrl], $actual->redirectedUrls());

        $this->assertSame($url, $actual->targetUrl());
        $this->assertSame($redirectedUrl, $actual->reachedUrl());
    }

    /**
     * 無効なURLを処理できる
     * @test
     */
    public function it_can_handle_invalid_url()
    {
        $url = 'http://www.goo000gle.com/';
        
        $actual = UrlStatus::get($url);

        $this->assertFalse($actual->isValidUrl());
        $this->assertSame(0, $actual->code());
        $this->assertSame([], $actual->redirectedCode());
        $this->assertSame([], $actual->redirectedUrls());
        $this->assertSame($url, $actual->targetUrl());
        $this->assertSame('', $actual->reachedUrl());
    }

    /**
     * 関数名がパターン(/\Ais(\d{3})\z/)にマッチしていない時に例外を投げる
     * @expectedException        \LogicException
     * @expectedExceptionMessage Only isXXX functions are allowed
     * @test
     */
    public function it_throws_an_exception_when_function_name_not_matching_pattern()
    {
        $actual = UrlStatus::get('https://example.com');
        $actual->isHoge();
    }
}
