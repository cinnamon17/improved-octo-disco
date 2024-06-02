<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\BookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: "updated")]
#[ApiResource(
    normalizationContext: [
        'groups' => ['book']
    ],

    operations: [
        new Get(),
        new GetCollection()
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['Title' => SearchFilter::STRATEGY_PARTIAL])]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups('book')]
    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $Title = null;

    #[ORM\Column(name: "VolumeInfo", length: 100, nullable: true)]
    private ?string $VolumeInfo = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $Series = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $Periodical = null;

    #[Groups('book')]
    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $Author = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $Year = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $Edition = null;

    #[ORM\Column(length: 400, nullable: true)]
    private ?string $Publisher = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $City = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $Pages = null;

    #[Groups('book')]
    #[ORM\Column(name: "PagesInFile")]
    private ?int $PagesInFile = null;

    #[Groups('book')]
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $Language = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $Topic = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $Library = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $Issue = null;

    #[ORM\Column(length: 300, nullable: true)]
    #[Groups("book")]
    private ?string $Identifier = null;

    #[ORM\Column(length: 9, nullable: true)]
    private ?string $ISSN = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $ASIN = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $UDC = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $LBC = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $DDC = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $LCC = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $Doi = null;

    #[ORM\Column(name: "Googlebookid", length: 45, nullable: true)]
    private ?string $GoogleBookid = null;

    #[ORM\Column(name: "OpenLibraryID", length: 200, nullable: true)]
    private ?string $OpenLibraryID = null;

    #[ORM\Column(length: 10000, nullable: true)]
    private ?string $Commentary = null;

    #[ORM\Column(nullable: true)]
    private ?int $DPI = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $Color = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $Cleaned = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $Orientation = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $Paginated = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $Scanned = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $Bookmarked = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $Searchable = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $Filesize = null;

    #[Groups('book')]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $Extension = null;

    #[Groups('book')]
    #[ORM\Column(length: 32, nullable: true)]
    private ?string $MD5 = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $Generic = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $Visible = null;

    #[Groups("book")]
    #[ORM\Column(length: 733, nullable: true)]
    private ?string $Locator = null;

    #[ORM\Column(nullable: true)]
    private ?int $Local = null;

    #[ORM\Column(name: "TimeAdded", type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $TimeAdded = null;

    #[ORM\Column(name: "TimeLastModified", type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $TimeLastModified = null;

    #[ORM\Column(name: "CoverUrl", length: 200, nullable: true)]
    private ?string $CoverUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $Tags = null;

    #[ORM\Column(name: "IdentifierWODash", length: 300, nullable: true)]
    private ?string $IdentifierWODash = null;


    #[Groups("book")]
    public function getCover(): string
    {
        $base_url = 'http://library.lol/covers/';
        $cover = $this->getCoverUrl();

        return $base_url . $cover;
    }

    #[Groups("book")]
    public function getDownloadUrl(): string
    {
        $base_url = 'https://download.library.lol/main/';
        $folder = substr($this->getCoverUrl(), 0, strpos($this->getCoverUrl(), '/'));
        $md5 = $this->getMD5();
        $locator = $this->getLocator();

        return $base_url . $folder . '/' . $md5 . '/' . $locator;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $ID): static
    {
        $this->id = $ID;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(?string $Title): static
    {
        $this->Title = $Title;

        return $this;
    }

    public function getVolumeInfo(): ?string
    {
        return $this->VolumeInfo;
    }

    public function setVolumeInfo(?string $VolumeInfo): static
    {
        $this->VolumeInfo = $VolumeInfo;

        return $this;
    }

    public function getSeries(): ?string
    {
        return $this->Series;
    }

    public function setSeries(?string $Series): static
    {
        $this->Series = $Series;

        return $this;
    }

    public function getPeriodical(): ?string
    {
        return $this->Periodical;
    }

    public function setPeriodical(?string $Periodical): static
    {
        $this->Periodical = $Periodical;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->Author;
    }

    public function setAuthor(?string $Author): static
    {
        $this->Author = $Author;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->Year;
    }

    public function setYear(?string $Year): static
    {
        $this->Year = $Year;

        return $this;
    }

    public function getEdition(): ?string
    {
        return $this->Edition;
    }

    public function setEdition(?string $Edition): static
    {
        $this->Edition = $Edition;

        return $this;
    }

    public function getPublisher(): ?string
    {
        return $this->Publisher;
    }

    public function setPublisher(string $Publisher): static
    {
        $this->Publisher = $Publisher;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->City;
    }

    public function setCity(string $City): static
    {
        $this->City = $City;

        return $this;
    }

    public function getPages(): ?string
    {
        return $this->Pages;
    }

    public function setPages(string $Pages): static
    {
        $this->Pages = $Pages;

        return $this;
    }

    public function getPagesInFile(): ?int
    {
        return $this->PagesInFile;
    }

    public function setPagesInFile(int $PagesInFile): static
    {
        $this->PagesInFile = $PagesInFile;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->Language;
    }

    public function setLanguage(?string $Language): static
    {
        $this->Language = $Language;

        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->Topic;
    }

    public function setTopic(?string $Topic): static
    {
        $this->Topic = $Topic;

        return $this;
    }

    public function getLibrary(): ?string
    {
        return $this->Library;
    }

    public function setLibrary(?string $Library): static
    {
        $this->Library = $Library;

        return $this;
    }

    public function getIssue(): ?string
    {
        return $this->Issue;
    }

    public function setIssue(?string $Issue): static
    {
        $this->Issue = $Issue;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->Identifier;
    }

    public function setIdentifier(?string $Identifier): static
    {
        $this->Identifier = $Identifier;

        return $this;
    }

    public function getISSN(): ?string
    {
        return $this->ISSN;
    }

    public function setISSN(?string $ISSN): static
    {
        $this->ISSN = $ISSN;

        return $this;
    }

    public function getASIN(): ?string
    {
        return $this->ASIN;
    }

    public function setASIN(?string $ASIN): static
    {
        $this->ASIN = $ASIN;

        return $this;
    }

    public function getUDC(): ?string
    {
        return $this->UDC;
    }

    public function setUDC(?string $UDC): static
    {
        $this->UDC = $UDC;

        return $this;
    }

    public function getLBC(): ?string
    {
        return $this->LBC;
    }

    public function setLBC(?string $LBC): static
    {
        $this->LBC = $LBC;

        return $this;
    }

    public function getDDC(): ?string
    {
        return $this->DDC;
    }

    public function setDDC(?string $DDC): static
    {
        $this->DDC = $DDC;

        return $this;
    }

    public function getLCC(): ?string
    {
        return $this->LCC;
    }

    public function setLCC(?string $LCC): static
    {
        $this->LCC = $LCC;

        return $this;
    }

    public function getDoi(): ?string
    {
        return $this->Doi;
    }

    public function setDoi(?string $Doi): static
    {
        $this->Doi = $Doi;

        return $this;
    }

    public function getGoogleBookid(): ?string
    {
        return $this->GoogleBookid;
    }

    public function setGoogleBookid(?string $GoogleBookid): static
    {
        $this->GoogleBookid = $GoogleBookid;

        return $this;
    }

    public function getOpenLibraryID(): ?string
    {
        return $this->OpenLibraryID;
    }

    public function setOpenLibraryID(?string $OpenLibraryID): static
    {
        $this->OpenLibraryID = $OpenLibraryID;

        return $this;
    }

    public function getCommentary(): ?string
    {
        return $this->Commentary;
    }

    public function setCommentary(?string $Commentary): static
    {
        $this->Commentary = $Commentary;

        return $this;
    }

    public function getDPI(): ?int
    {
        return $this->DPI;
    }

    public function setDPI(?int $DPI): static
    {
        $this->DPI = $DPI;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->Color;
    }

    public function setColor(?string $Color): static
    {
        $this->Color = $Color;

        return $this;
    }

    public function getCleaned(): ?string
    {
        return $this->Cleaned;
    }

    public function setCleaned(?string $Cleaned): static
    {
        $this->Cleaned = $Cleaned;

        return $this;
    }

    public function getOrientation(): ?string
    {
        return $this->Orientation;
    }

    public function setOrientation(?string $Orientation): static
    {
        $this->Orientation = $Orientation;

        return $this;
    }

    public function getPaginated(): ?string
    {
        return $this->Paginated;
    }

    public function setPaginated(?string $Paginated): static
    {
        $this->Paginated = $Paginated;

        return $this;
    }

    public function getScanned(): ?string
    {
        return $this->Scanned;
    }

    public function setScanned(?string $Scanned): static
    {
        $this->Scanned = $Scanned;

        return $this;
    }

    public function getBookmarked(): ?string
    {
        return $this->Bookmarked;
    }

    public function setBookmarked(?string $Bookmarked): static
    {
        $this->Bookmarked = $Bookmarked;

        return $this;
    }

    public function getSearchable(): ?string
    {
        return $this->Searchable;
    }

    public function setSearchable(?string $Searchable): static
    {
        $this->Searchable = $Searchable;

        return $this;
    }

    public function getFilesize(): ?string
    {
        return $this->Filesize;
    }

    public function setFilesize(string $Filesize): static
    {
        $this->Filesize = $Filesize;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->Extension;
    }

    public function setExtension(?string $Extension): static
    {
        $this->Extension = $Extension;

        return $this;
    }

    public function getMD5(): ?string
    {
        return $this->MD5;
    }

    public function setMD5(?string $MD5): static
    {
        $this->MD5 = $MD5;

        return $this;
    }

    public function getGeneric(): ?string
    {
        return $this->Generic;
    }

    public function setGeneric(?string $Generic): static
    {
        $this->Generic = $Generic;

        return $this;
    }

    public function getVisible(): ?string
    {
        return $this->Visible;
    }

    public function setVisible(?string $Visible): static
    {
        $this->Visible = $Visible;

        return $this;
    }

    public function getLocator(): ?string
    {
        return $this->Locator;
    }

    public function setLocator(?string $Locator): static
    {
        $this->Locator = $Locator;

        return $this;
    }

    public function getLocal(): ?int
    {
        return $this->Local;
    }

    public function setLocal(?int $Local): static
    {
        $this->Local = $Local;

        return $this;
    }

    public function getTimeAdded(): ?\DateTimeInterface
    {
        return $this->TimeAdded;
    }

    public function setTimeAdded(\DateTimeInterface $TimeAdded): static
    {
        $this->TimeAdded = $TimeAdded;

        return $this;
    }

    public function getTimeLastModified(): ?\DateTimeInterface
    {
        return $this->TimeLastModified;
    }

    public function setTimeLastModified(\DateTimeInterface $TimeLastModified): static
    {
        $this->TimeLastModified = $TimeLastModified;

        return $this;
    }

    public function getCoverUrl(): ?string
    {
        return $this->CoverUrl;
    }

    public function setCoverUrl(?string $CoverUrl): static
    {
        $this->CoverUrl = $CoverUrl;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->Tags;
    }

    public function setTags(?string $Tags): static
    {
        $this->Tags = $Tags;

        return $this;
    }

    public function getIdentifierWODash(): ?string
    {
        return $this->IdentifierWODash;
    }

    public function setIdentifierWODash(?string $IdentifierWODash): static
    {
        $this->IdentifierWODash = $IdentifierWODash;

        return $this;
    }
}
