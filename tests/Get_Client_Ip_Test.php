<?php
class Get_Client_Ip_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Get_Client_Ip
     */
    protected $getClientIp;

    public function testClassExists()
    {
        $this->assertTrue(class_exists('Get_Client_Ip'));
    }

    public function setUp()
    {
        $this->getClientIp = new Get_Client_Ip;
    }

    protected function tearDown()
    {
        $this->getClientIp = null;
    }

    /**
     * @covers Get_Client_Ip::getScriptVersion
     * @covers Get_Client_Ip::setServerHeaders
     * @covers Get_Client_Ip::getServerHeaders
     */
    public function testBasicMethods()
    {
        $this->assertNotEmpty($this->getClientIp->getScriptVersion());

        $this->getClientIp->setServerHeaders(array("QUERY_STRING"          => "h=examplequery",
                                                 "REQUEST_METHOD"        => "GET",
                                                 "SCRIPT_NAME"           => "/index.php",
                                                 "SERVER_PROTOCOL"       => "HTTP/1.1",
                                                 "GATEWAY_INTERFACE"     => "CGI/1.1",
                                                 "REMOTE_ADDR"           => "1.2.3.4",
                                                 "REMOTE_PORT"           => "",
                                                 "SERVER_ADDR"           => "1.1.1.1",
                                                 "SERVER_PORT"           => "80",
                                                 "X_FORWARDED_FOR"       => "2.3.4.5,1.2.3.4, 1.2.3.4",
                                                 "HTTP_X_FORWARDED_FOR"  => "2.3.4.5,1.2.3.4",
                                                 "HTTP_USER_AGENT"       => "Opera/9.80 (J2ME/MIDP; Opera Mini/4.1.13906/37.7886; U; ru) Presto/2.12.423 Version/12.16",
                                                 "HTTP_FORWARDED"        => "for=\"2.3.4.5:20931\"",
                                                 "HTTP_CF_CONNECTING_IP" => "1.2.3.4"
        ));

        //12 because only have IP
        $this->assertCount( 4, $this->getClientIp->getServerHeaders() );
        $this->assertNotEmpty( $this->getClientIp->getClientIp() );
    }

    public function validIpProvider()
    {
        return array(
            array('127.0.0.1', false),
            array('10.0.0.1', false),
            array('255.255.255.255', false),
            array('test', false),
            array('8.8.8.8', true),
            array('2001:0db8:85a3:08d3:1319:8a2e:0370:7334', false)
        );
    }

    /**
     * @dataProvider validIpProvider
     * @covers Get_Client_Ip::validate_ip
     */
    public function testValidIp($ip, $expectedVal)
    {
        $md = new Get_Client_Ip();
        $this->assertSame($expectedVal, $md->validate_ip($ip));
    }

    public function ipProvider()
    {
        return array(
            array(array(
                      'HTTP_X_FORWARDED_FOR' => '8.8.8.8,8.8.4.4'
                  ), '8.8.8.8'),
            array(array(
                      "REMOTE_ADDR"           => "8.8.4.4",
                      "X_FORWARDED_FOR"       => "8.8.8.8,8.8.4.4, 8.8.4.4",
                  ), '8.8.8.8'),
            array(array(
                      "REMOTE_ADDR"           => "8.8.4.4",
                      "X_FORWARDED_FOR"       => "127.0.0.1,8.8.4.4, 8.8.4.4",
                  ), '8.8.4.4'),
            array(array(
                      "REMOTE_ADDR"           => "8.8.4.4",
                      "X_FORWARDED_FOR"       => "127.0.0.1,2001:0db8:85a3:08d3:1319:8a2e:0370:7334, 8.8.8.8",
                  ), '8.8.8.8'),
            array(array(), false)
        );
    }

    /**
     * @dataProvider ipProvider
     * @covers Get_Client_Ip::setServerHeaders
     * @covers Get_Client_Ip::getClientIp
     */
    public function testGetClientIp($headers, $expectedIp)
    {
        $md = new Get_Client_Ip($headers);
        $this->assertSame($expectedIp, $md->getClientIp());
    }
}