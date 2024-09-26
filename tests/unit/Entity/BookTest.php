<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Book;
use PHPUnit\Framework\TestCase;

class BookTest extends TestCase
{
    public function testGetIdAndSetId()
    {
        $book = new Book();
        $book->setId(1);
        $this->assertEquals(1, $book->getId());
    }

    public function testGetTitleAndSetTitle()
    {
        $book = new Book();
        $book->setTitle('Test Book');
        $this->assertEquals('Test Book', $book->getTitle());
    }

    public function testGetVolumeInfoAndSetVolumeInfo()
    {
        $book = new Book();
        $book->setVolumeInfo('Volume Info');
        $this->assertEquals('Volume Info', $book->getVolumeInfo());
    }

    public function testGetSeriesAndSetSeries()
    {
        $book = new Book();
        $book->setSeries('Series Name');
        $this->assertEquals('Series Name', $book->getSeries());
    }

    public function testGetPeriodicalAndSetPeriodical()
    {
        $book = new Book();
        $book->setPeriodical('Periodical Name');
        $this->assertEquals('Periodical Name', $book->getPeriodical());
    }

    public function testGetAuthorAndSetAuthor()
    {
        $book = new Book();
        $book->setAuthor('Author Name');
        $this->assertEquals('Author Name', $book->getAuthor());
    }

    public function testGetYearAndSetYear()
    {
        $book = new Book();
        $book->setYear('2020');
        $this->assertEquals('2020', $book->getYear());
    }

    public function testGetEditionAndSetEdition()
    {
        $book = new Book();
        $book->setEdition('First Edition');
        $this->assertEquals('First Edition', $book->getEdition());
    }

    public function testGetPublisherAndSetPublisher()
    {
        $book = new Book();
        $book->setPublisher('Publisher Name');
        $this->assertEquals('Publisher Name', $book->getPublisher());
    }

    public function testGetCityAndSetCity()
    {
        $book = new Book();
        $book->setCity('City Name');
        $this->assertEquals('City Name', $book->getCity());
    }

    public function testGetPagesAndSetPages()
    {
        $book = new Book();
        $book->setPages('300');
        $this->assertEquals('300', $book->getPages());
    }

    public function testGetPagesInFileAndSetPagesInFile()
    {
        $book = new Book();
        $book->setPagesInFile(350);
        $this->assertEquals(350, $book->getPagesInFile());
    }

    public function testGetLanguageAndSetLanguage()
    {
        $book = new Book();
        $book->setLanguage('English');
        $this->assertEquals('English', $book->getLanguage());
    }

    public function testGetTopicAndSetTopic()
    {
        $book = new Book();
        $book->setTopic('Technology');
        $this->assertEquals('Technology', $book->getTopic());
    }

    public function testGetLibraryAndSetLibrary()
    {
        $book = new Book();
        $book->setLibrary('Library Name');
        $this->assertEquals('Library Name', $book->getLibrary());
    }

    public function testGetIssueAndSetIssue()
    {
        $book = new Book();
        $book->setIssue('Issue 4');
        $this->assertEquals('Issue 4', $book->getIssue());
    }

    public function testGetIdentifierAndSetIdentifier()
    {
        $book = new Book();
        $book->setIdentifier('Identifier');
        $this->assertEquals('Identifier', $book->getIdentifier());
    }

    public function testGetISSNAndSetISSN()
    {
        $book = new Book();
        $book->setISSN('12345678');
        $this->assertEquals('12345678', $book->getISSN());
    }

    public function testGetASINAndSetASIN()
    {
        $book = new Book();
        $book->setASIN('ASIN123');
        $this->assertEquals('ASIN123', $book->getASIN());
    }

    public function testGetUDCAndSetUDC()
    {
        $book = new Book();
        $book->setUDC('UDC Code');
        $this->assertEquals('UDC Code', $book->getUDC());
    }

    public function testGetLBCAndSetLBC()
    {
        $book = new Book();
        $book->setLBC('LBC Code');
        $this->assertEquals('LBC Code', $book->getLBC());
    }

    public function testGetDDCAndSetDDC()
    {
        $book = new Book();
        $book->setDDC('DDC Code');
        $this->assertEquals('DDC Code', $book->getDDC());
    }

    public function testGetLCCAndSetLCC()
    {
        $book = new Book();
        $book->setLCC('LCC Code');
        $this->assertEquals('LCC Code', $book->getLCC());
    }

    public function testGetDoiAndSetDoi()
    {
        $book = new Book();
        $book->setDoi('10.1234/abcde');
        $this->assertEquals('10.1234/abcde', $book->getDoi());
    }

    public function testGetGoogleBookidAndSetGoogleBookid()
    {
        $book = new Book();
        $book->setGoogleBookid('GoogleBookID123');
        $this->assertEquals('GoogleBookID123', $book->getGoogleBookid());
    }

    public function testGetOpenLibraryIDAndSetOpenLibraryID()
    {
        $book = new Book();
        $book->setOpenLibraryID('OL123M');
        $this->assertEquals('OL123M', $book->getOpenLibraryID());
    }

    public function testGetCommentaryAndSetCommentary()
    {
        $book = new Book();
        $book->setCommentary('Some commentary');
        $this->assertEquals('Some commentary', $book->getCommentary());
    }

    public function testGetDPIAndSetDPI()
    {
        $book = new Book();
        $book->setDPI(300);
        $this->assertEquals(300, $book->getDPI());
    }

    public function testGetColorAndSetColor()
    {
        $book = new Book();
        $book->setColor('Y');
        $this->assertEquals('Y', $book->getColor());
    }

    public function testGetCleanedAndSetCleaned()
    {
        $book = new Book();
        $book->setCleaned('N');
        $this->assertEquals('N', $book->getCleaned());
    }

    public function testGetOrientationAndSetOrientation()
    {
        $book = new Book();
        $book->setOrientation('P');
        $this->assertEquals('P', $book->getOrientation());
    }

    public function testGetPaginatedAndSetPaginated()
    {
        $book = new Book();
        $book->setPaginated('Y');
        $this->assertEquals('Y', $book->getPaginated());
    }

    public function testGetScannedAndSetScanned()
    {
        $book = new Book();
        $book->setScanned('Y');
        $this->assertEquals('Y', $book->getScanned());
    }

    public function testGetBookmarkedAndSetBookmarked()
    {
        $book = new Book();
        $book->setBookmarked('Y');
        $this->assertEquals('Y', $book->getBookmarked());
    }

    public function testGetSearchableAndSetSearchable()
    {
        $book = new Book();
        $book->setSearchable('N');
        $this->assertEquals('N', $book->getSearchable());
    }

    public function testGetFilesizeAndSetFilesize()
    {
        $book = new Book();
        $book->setFilesize('2048');
        $this->assertEquals('2048', $book->getFilesize());
    }

    public function testGetExtensionAndSetExtension()
    {
        $book = new Book();
        $book->setExtension('pdf');
        $this->assertEquals('pdf', $book->getExtension());
    }

    public function testGetMD5AndSetMD5()
    {
        $book = new Book();
        $book->setMD5('a1b2c3d4e5f6g7h8');
        $this->assertEquals('a1b2c3d4e5f6g7h8', $book->getMD5());
    }

    public function testGetGenericAndSetGeneric()
    {
        $book = new Book();
        $book->setGeneric('GenericValue');
        $this->assertEquals('GenericValue', $book->getGeneric());
    }

    public function testGetVisibleAndSetVisible()
    {
        $book = new Book();
        $book->setVisible('Y');
        $this->assertEquals('Y', $book->getVisible());
    }

    public function testGetLocatorAndSetLocator()
    {
        $book = new Book();
        $book->setLocator('Locator/Value/Path');
        $this->assertEquals('Locator/Value/Path', $book->getLocator());
    }

    public function testGetLocalAndSetLocal()
    {
        $book = new Book();
        $book->setLocal(1);
        $this->assertEquals(1, $book->getLocal());
    }

    public function testGetTimeAddedAndSetTimeAdded()
    {
        $book = new Book();
        $timeAdded = new \DateTime();
        $book->setTimeAdded($timeAdded);
        $this->assertEquals($timeAdded, $book->getTimeAdded());
    }

    public function testGetTimeLastModifiedAndSetTimeLastModified()
    {
        $book = new Book();
        $timeLastModified = new \DateTime();
        $book->setTimeLastModified($timeLastModified);
        $this->assertEquals($timeLastModified, $book->getTimeLastModified());
    }

    public function testGetCoverUrlAndSetCoverUrl()
    {
        $book = new Book();
        $book->setCoverUrl('cover.jpg');
        $this->assertEquals('cover.jpg', $book->getCoverUrl());
    }

    public function testGetTagsAndSetTags()
    {
        $book = new Book();
        $book->setTags('Tag1, Tag2');
        $this->assertEquals('Tag1, Tag2', $book->getTags());
    }

    public function testGetIdentifierWODashAndSetIdentifierWODash()
    {
        $book = new Book();
        $book->setIdentifierWODash('IdentifierWithoutDash');
        $this->assertEquals('IdentifierWithoutDash', $book->getIdentifierWODash());
    }

    public function testGetCover()
    {
        $book = new Book();
        $book->setCoverUrl('cover.jpg');
        $this->assertEquals('http://library.lol/covers/cover.jpg', $book->getCover());
    }

    public function testGetDownloadUrl()
    {
        $book = new Book();
        $book->setCoverUrl('0/cover.jpg');
        $book->setMD5('a1b2c3d4e5f6g7h8');
        $book->setLocator('Locator/Value/Path');

        $this->assertEquals('https://download.library.lol/main/0/a1b2c3d4e5f6g7h8/Locator/Value/Path', $book->getDownloadUrl());
    }
}
