<?php

namespace App\Tests\Template;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class AnniversaryTemplateTest extends KernelTestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->twig = static::getContainer()->get(Environment::class);
    }

    public function testAnniversaryTemplateWithCurrentMonthPlayer(): void
    {
        $currentMonth = (new \DateTime())->format('m');
        $birthDateThisMonth = new \DateTime('1995-' . $currentMonth . '-15');
        $birthDateOtherMonth = new \DateTime('1995-' . ($currentMonth == '01' ? '02' : '01') . '-10');

        $user1 = [
            'id' => '123',
            'firstName' => 'Thomas',
            'lastName' => 'Breton',
            'fullName' => 'Thomas Breton',
            'birthDate' => $birthDateThisMonth,
            'photo' => 'thomas.jpg',
            'profilePicture' => 'thomas.jpg',
        ];

        $user2 = [
            'id' => '456',
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'fullName' => 'Jean Dupont',
            'birthDate' => $birthDateOtherMonth,
            'photo' => null,
            'profilePicture' => null,
        ];

        // Test with listAnniversaires (pre-filtered)
        $html = $this->twig->render('default/anniversary.html.twig', [
            'listAnniversaires' => [$user1],
        ]);

        $this->assertStringContainsString('Thomas Breton', $html);
        $this->assertStringContainsString('col-6', $html);
        $this->assertStringContainsString('uploads/users/thomas.jpg', $html);
        $this->assertStringNotContainsString('Jean Dupont', $html);

        // Test with listEffectif (filtering done in Twig if listAnniversaires is not provided)
        $htmlFromEffectif = $this->twig->render('default/anniversary.html.twig', [
            'listEffectif' => [$user1, $user2],
        ]);

        $this->assertStringContainsString('Thomas Breton', $htmlFromEffectif);
        $this->assertStringContainsString('uploads/users/thomas.jpg', $htmlFromEffectif);
        $this->assertStringNotContainsString('Jean Dupont', $htmlFromEffectif);

        // Test with empty list
        $htmlEmpty = $this->twig->render('default/anniversary.html.twig', [
            'listAnniversaires' => [],
        ]);

        $this->assertStringContainsString('Aucun anniversaire ce mois-ci', $htmlEmpty);
    }

    public function testAnniversaryTemplateWithNoPhotoUsesFallback(): void
    {
        $currentMonth = (new \DateTime())->format('m');
        $birthDateThisMonth = new \DateTime('2000-' . $currentMonth . '-20');

        $userWithoutPhoto = [
            'id' => '789',
            'firstName' => 'Paul',
            'lastName' => 'Martin',
            'fullName' => 'Paul Martin',
            'birthDate' => $birthDateThisMonth,
            'photo' => null,
            'profilePicture' => null,
        ];

        $html = $this->twig->render('default/anniversary.html.twig', [
            'listAnniversaires' => [$userWithoutPhoto],
        ]);

        $this->assertStringContainsString('Paul Martin', $html);
        $this->assertStringContainsString('images/no-user-image.gif', $html);
    }
}
