<?php

/**
 * Test website parser.
 * @author Christian Blank <c.blank@notthatbad.net>
 */
class WebsiteParserTest extends SapphireTest {



    public function setUpOnce() {
        Injector::nest();
        Injector::inst()->registerService(new TestFetcher(), 'Fetcher');
        parent::setUpOnce();
    }

    public function tearDownOnce() {
        Injector::unnest();
        parent::tearDownOnce();
    }

    public function testNotFound() {
        $result = WebsiteParser::parse("http://not.a-site");
        $this->assertTrue($result->isError());
    }

    public function testNormalSite() {
        $result = WebsiteParser::parse("http://normal.site");
        $this->assertFalse($result->isError());
        $this->assertFalse($result->hasImage());
        $this->assertEquals("Normal Site", $result->Title);
        $this->assertEquals("The description", $result->Description);
    }

}

class TestFetcher implements IFetcher, TestOnly {
    const HTML_DOC = <<<'EOT'
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Normal Site</title>
    <meta name="description" content="The description">
</head>
<body>

</body>
</html>
EOT;

    private static $data = [
        "http://normal.site" => TestFetcher::HTML_DOC,
    ];

    public function fetch($url) {
        if(array_key_exists($url, self::$data)) {
            return self::$data[$url];
        }
        throw new Exception("Website not found");

    }
}
