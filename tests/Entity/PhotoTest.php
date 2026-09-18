<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Competition;
use App\Entity\Photo;
use App\Entity\Season;
use PHPUnit\Framework\TestCase;

class PhotoTest extends TestCase
{
    public function testPhotoProperties(): void
    {
        $photo = new Photo();
        $season = new Season();
        $season->setLabel('2023-2024');

        $category = new Category();
        $competition = new Competition();

        $photo->setTitle('Match amical')
            ->setFileName('photo_test.jpg')
            ->setSeason($season)
            ->setCategory($category)
            ->setCompetition($competition);

        $this->assertSame('Match amical', $photo->getTitle());
        $this->assertSame('photo_test.jpg', $photo->getFileName());
        $this->assertSame($season, $photo->getSeason());
        $this->assertSame($category, $photo->getCategory());
        $this->assertSame($competition, $photo->getCompetition());
        $this->assertSame('uploads/2023-2024/photo_test.jpg', $photo->getImagePath());
        $this->assertSame('Match amical', (string) $photo);
    }
}
