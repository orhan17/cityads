<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\CityAdsSyncService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Пример теста для методов CityAdsSyncService, которые не требуют обращения к БД
 */
class CityAdsSyncServiceTest extends TestCase
{
    public function testHasWrldReturnsTrue()
    {
        $service = $this->createService();

        $geoList = [
            ['code' => 'RU'],
            ['code' => 'Wrld'],
        ];

        $result = $this->callHasWrld($service, $geoList);

        // 4) Проверяем, что вернёт true
        $this->assertTrue($result, 'Ожидаем, что hasWrld вернёт true');
    }

    public function testHasWrldReturnsFalse()
    {
        $service = $this->createService();

        $geoList = [
            ['code' => 'RU'],
            ['code' => 'US'],
        ];

        $result = $this->callHasWrld($service, $geoList);

        $this->assertFalse($result, 'Ожидаем, что нет Wrld в списке');
    }

    public function testCalculateRatingBasic()
    {
        $service = $this->createService();

        // approvalTime=30, paymentTime=10
        // coeff1 = 10 * (1 - 30/90) = 10 * (1 - 0.3333) = 10 * 0.6667 = ~6.6667
        // coeff2 = 100 * (1 - 10/90) = 100 * (1 - 0.1111) = 100 * 0.8889 = ~88.8889
        // ecpl=2
        // rating ~ 2 * 6.6667 * 88.8889 = ~1185.19
        // Тестируемм округление / точность как минимум по сравнению > 1000

        $result = $this->callCalculateRating($service, 2.0, 30, 10);

        $this->assertGreaterThan(1000, $result, 'Rating должен быть больше 1000');
    }

    public function testCalculateRatingZeroCoeffs()
    {
        $service = $this->createService();

        // Если approvalTime >= 90 -> coeff1 <= 0, тогда считаем coeff1=1
        // Если paymentTime >= 90 -> coeff2 <= 0, тогда coeff2=1

        // Кейс 1: approvalTime=120 -> coeff1 <= 0 => 1
        // paymentTime=10 -> coeff2 ~ 88.88
        // ecpl=1
        // => rating = 1 * 1 * 88.88 => ~88.88
        $rating = $this->callCalculateRating($service, 1.0, 120, 10);
        $this->assertGreaterThan(80, $rating);
        $this->assertLessThan(100, $rating);

        // Кейс 2: paymentTime=100 -> coeff2 <= 0 => 1
        // approvalTime=30 -> coeff1 ~ 6.66
        // ecpl=1
        // => rating = 1*6.66*1 => ~6.66
        $rating2 = $this->callCalculateRating($service, 1.0, 30, 100);
        $this->assertGreaterThan(6, $rating2);
        $this->assertLessThan(7, $rating2);

        // Кейс 3: approvalTime=200 -> coeff1=1; paymentTime=200 -> coeff2=1 => rating=ecpl*1*1
        $rating3 = $this->callCalculateRating($service, 2.0, 200, 200);
        $this->assertEquals(2.0, $rating3);
    }

    /**
     * Хелпер — создаёт service, мокируя зависимости
     */
    private function createService(): CityAdsSyncService
    {
        $mockHttp = $this->createMock(HttpClientInterface::class);
        $mockEm = $this->createMock(EntityManagerInterface::class);

        return new CityAdsSyncService($mockHttp, $mockEm);
    }

    /**
     * Хелпер — вызываем приватный метод hasWrld(...) через Reflection (или делаем публичным)
     */
    private function callHasWrld(CityAdsSyncService $service, array $geoList): bool
    {
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('hasWrld');
        $method->setAccessible(true);

        return $method->invoke($service, $geoList);
    }

    /**
     * Аналогично для calculateRating(...)
     */
    private function callCalculateRating(CityAdsSyncService $service, float $ecpl, int $approvalTime, int $paymentTime): float
    {
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('calculateRating');
        $method->setAccessible(true);

        return $method->invoke($service, $ecpl, $approvalTime, $paymentTime);
    }
}
