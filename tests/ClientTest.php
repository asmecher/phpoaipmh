<?php

require_once(__DIR__ . '/../src/Phpoaipmh/Http/Client.php');
require_once(__DIR__ . '/../src/Phpoaipmh/Http/RequestException.php');
require_once(__DIR__ . '/../src/Phpoaipmh/Client.php');
require_once(__DIR__ . '/../src/Phpoaipmh/OaipmhRequestException.php');

class ClientTest extends PHPUnit_Framework_TestCase {

    // -------------------------------------------------------------------------

    public function setUp()
    {
        parent::setUp();
    }

    // -------------------------------------------------------------------------

    public function tearDown()
    {
        parent::tearDown();
    }

    // -------------------------------------------------------------------------

    /**
     * Simple Instantiation Test
     *
     * Tests that no syntax or runtime errors occur during object insantiation
     */
    public function testInsantiateCreatesNewObject()
    {    
        $obj = new Phpoaipmh\Client('http://example.com/oai', new HttpMockClient);
        $this->assertInstanceOf('Phpoaipmh\Client', $obj);
    }

    // -------------------------------------------------------------------------

    /**
     * Test that a simple valid response is decoded correctly
     */
    public function testRequestDecodesValidResponseCorrectly()
    {
        $mockClient = new HttpMockClient;
        $mockClient->toReturn = '<?xml version="1.0" encoding="UTF-8" ?><OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd"><responseDate>2012-08-06T19:25:47Z</responseDate><request verb="Identify">http://nsdl.org/oai</request><Identify><repositoryName>National Science Digital Library</repositoryName><baseURL>http://nsdl.org/oai</baseURL><protocolVersion>2.0</protocolVersion><adminEmail>jweather@ucar.edu</adminEmail><earliestDatestamp>1900-01-01T12:00:00Z</earliestDatestamp><deletedRecord>no</deletedRecord><granularity>YYYY-MM-DDThh:mm:ssZ</granularity> <compression>gzip</compression><description><oai-identifier    xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier"   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"   xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier   http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">  <scheme>oai</scheme>   <repositoryIdentifier>nsdl.org</repositoryIdentifier>      <delimiter>:</delimiter>   <sampleIdentifier>oai:nsdl.org:1477460</sampleIdentifier></oai-identifier></description><description>    <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">        <dc:description>The National Science Digital Library (NSDL) is a national network of digital environments dedicated to advancing science, technology, engineering, and mathematics (STEM) teaching and learning for all learners, in both formal and informal settings.</dc:description>    </oai_dc:dc></description></Identify></OAI-PMH>';
        $obj = new Phpoaipmh\Client('http://nsdl.org/oai', $mockClient);
        $result = $obj->request('Identify');

        //Check correct object type and result
        $this->assertInstanceOf('SimpleXMLElement', $result);
        $this->assertTrue(isset($result->Identify));
    }

    // -------------------------------------------------------------------------

    /**
     * Test that the client throws a Http\RequestException for non-XML or non-parsable responses
     */
    public function testInvalidXMLResponseThrowsHttpRequestException()
    {
        $mockClient = new HttpMockClient;
        $mockClient->toReturn = 'thisIs&NotXML!!';
        $this->setExpectedException('Phpoaipmh\Http\RequestException');

        $obj = new Phpoaipmh\Client('http://nsdl.org/oai', $mockClient);
        $obj->request('Identify');
    }

    // -------------------------------------------------------------------------

    /**
     * Test that a XML response with a OAI-PMH error embedded throws an exception
     */
    public function testRequestThrowsOAIPMHExceptionForInvalidVerbOrParams()
    {
        $mockClient = new HttpMockClient;
        $mockClient->toReturn = '<?xml version="1.0" encoding="UTF-8" ?>  <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/  http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd"> <responseDate>2012-08-06T19:33:31Z</responseDate> <request>http://nsdl.org/oai</request>      <error code="badVerb">The verb &#39;NotExist&#39; is illegal</error>  </OAI-PMH>';
        $this->setExpectedException('Phpoaipmh\OaipmhRequestException');

        $obj = new Phpoaipmh\Client('http://nsdl.org/oai', $mockClient);
        $obj->request('NonexistentVerb');
    }
}

// =============================================================================

class HttpMockClient implements Phpoaipmh\Http\Client
{
    public $toReturn = '';

    public function request($url)
    {
        return $this->toReturn;
    }
}


/* EOF: ClientTest.php */