<?php

namespace ArsamMe\Wallet\Test\Unit;

use ArsamMe\Wallet\Contracts\Services\MathServiceInterface;
use ArsamMe\Wallet\Test\TestCase;
use Brick\Math\Exception\NumberFormatException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
final class MathServiceTest extends TestCase {
    #[DataProvider('invalidProvider')]
    public function test_abs_invalid(string $value): void {
        $this->expectException(NumberFormatException::class);

        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);
        $provider->abs($value);
    }

    public function test_abs(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // int
        self::assertSame(123, (int) $provider->abs(123));
        self::assertSame(123, (int) $provider->abs(-123));

        // float
        self::assertSame(0, (int) $provider->abs(.0));
        self::assertSame(123, (int) $provider->abs(123.0));
        self::assertSame(123.11, (float) $provider->abs(123.11));
        self::assertSame(123.11, (float) $provider->abs(-123.11));

        // string
        // brick/math 0.9+
        self::assertSame(123, (int) $provider->abs('123.'));
        self::assertSame(.11, (float) $provider->abs('.11'));

        self::assertSame(123.11, (float) $provider->abs('123.11'));
        self::assertSame(123.11, (float) $provider->abs('-123.11'));
    }

    public function test_compare(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // int
        self::assertSame(0, $provider->compare(1, 1));
        self::assertSame(-1, $provider->compare(1, 2));
        self::assertSame(1, $provider->compare(2, 1));

        // float
        self::assertSame(0, $provider->compare(1.33, 1.33));
        self::assertSame(-1, $provider->compare(1.44, 2));
        self::assertSame(1, $provider->compare(2, 1.44));

        // string
        self::assertSame(0, $provider->compare('1.33', '1.33'));
        self::assertSame(-1, $provider->compare('1.44', '2'));
        self::assertSame(1, $provider->compare('2', '1.44'));
    }

    public function test_add(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // int
        self::assertSame(0, $provider->compare($provider->add(1, 5), 6));
        self::assertSame(0, $provider->compare($provider->add(-1, 5), 4));

        // float
        self::assertSame(0, $provider->compare($provider->add(1.17, 4.83), 6.));
        self::assertSame(0, $provider->compare($provider->add(-1.44, 5.43), 3.99));

        self::assertSame(
            0,
            $provider->compare(
                $provider->add('4.331733759839529271053448625299468628', 1.4),
                '5.731733759839529271053448625299468628'
            )
        );

        self::assertSame(
            0,
            $provider->compare(
                $provider->add('5.731733759839529271053448625299468628', '-5.731733759839529271053448625299468627'),
                '0.000000000000000000000000000000000001'
            )
        );
    }

    public function test_sub(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // int
        self::assertSame(-4, (int) $provider->sub(1, 5));
        self::assertSame(-6, (int) $provider->sub(-1, 5));

        // float
        self::assertSame(-3.66, (float) $provider->sub(1.17, 4.83));
        self::assertSame(-6.87, (float) $provider->sub(-1.44, 5.43));

        self::assertSame(
            0,
            $provider->compare(
                $provider->sub('4.331733759839529271053448625299468628', 1.4),
                '2.931733759839529271053448625299468628'
            )
        );

        self::assertSame(
            0,
            $provider->compare(
                $provider->sub('5.731733759839529271053448625299468628', '5.731733759839529271053448625299468627'),
                '0.000000000000000000000000000000000001'
            )
        );
    }

    public function test_div(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // int
        self::assertSame(0.2, (float) $provider->div(1, 5));
        self::assertSame(-0.2, (float) $provider->div(-1, 5));

        // float
        self::assertSame(0.24223602484472, (float) $provider->div(1.17, 4.83, 14));
        self::assertSame(-0.26519337016574, (float) $provider->div(-1.44, 5.43, 14));

        self::assertSame(
            0,
            $provider->compare(
                $provider->div('4.331733759839529271053448625299468628', 1.4),
                '3.0940955427425209078953204466424775914285714285714285714285714285'
            )
        );

        self::assertSame(
            0,
            $provider->compare(
                $provider->div('5.731733759839529271053448625299468628', '5.731733759839529271053448625299468627'),
                '1.0000000000000000000000000000000000001744672802157504419105369811'
            )
        );
    }

    public function test_mul(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // int
        self::assertSame(5, (int) $provider->mul(1, 5));
        self::assertSame(-5, (int) $provider->mul(-1, 5));

        // float
        self::assertSame(5.6511, (float) $provider->mul(1.17, 4.83));
        self::assertSame(-7.8192, (float) $provider->mul(-1.44, 5.43));

        self::assertSame(
            0,
            $provider->compare(
                $provider->mul('4.331733759839529271053448625299468628', 1.4),
                '6.0644272637753409794748280754192560792000000000000000000000000000'
            )
        );

        self::assertSame(
            0,
            $provider->compare(
                $provider->mul('5.731733759839529271053448625299468628', '5.731733759839529271053448625299468627'),
                '32.8527718936841866108362353549577464458763784076112941028307058338'
            )
        );
    }

    public function test_pow(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // int
        self::assertSame(1, (int) $provider->pow(1, 5));
        self::assertSame(-1, (int) $provider->pow(-1, 5));

        // float
        self::assertSame(1.87388721, (float) $provider->pow(1.17, 4));
        self::assertSame(-6.1917364224, (float) $provider->pow(-1.44, 5));

        self::assertSame(
            0,
            $provider->compare(
                $provider->pow('4.331733759839529271053448625299468628', 14),
                '818963567.1194514424328910747572247977826032927674623819207642247854744523'
            )
        );

        self::assertSame(
            0,
            $provider->compare(
                $provider->pow('5.731733759839529271053448625299468628', 6),
                '35458.1485207464760293448564751702377579632773756221209731837301291644'
            )
        );
    }

    public function test_ceil(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // positive
        // int
        self::assertSame('35458', $provider->ceil(35458));

        // float
        self::assertSame('35458', $provider->ceil('35458.00000000'));
        self::assertSame('35459', $provider->ceil(35458.0000001));
        self::assertSame('35459', $provider->ceil(35458.4));
        self::assertSame('35459', $provider->ceil(35458.5));
        self::assertSame('35459', $provider->ceil(35458.6));

        // string
        self::assertSame(
            '35459',
            $provider->ceil('35458.1485207464760293448564751702377579632773756221209731837301291644')
        );

        // negative
        // int
        self::assertSame('-35458', $provider->ceil(-35458));

        // float
        self::assertSame('-35458', $provider->ceil(-35458.0000001));
        self::assertSame('-35458', $provider->ceil(-35458.4));
        self::assertSame('-35458', $provider->ceil(-35458.5));
        self::assertSame('-35458', $provider->ceil(-35458.6));

        // string
        self::assertSame(
            '-35458',
            $provider->ceil('-35458.1485207464760293448564751702377579632773756221209731837301291644')
        );
    }

    public function test_floor(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // positive
        // int
        self::assertSame('35458', $provider->floor(35458));

        // float
        self::assertSame('35458', $provider->floor('35458.00000000'));
        self::assertSame('35458', $provider->floor(35458.0000001));
        self::assertSame('35458', $provider->floor(35458.4));
        self::assertSame('35458', $provider->floor(35458.5));
        self::assertSame('35458', $provider->floor(35458.6));

        // string
        self::assertSame(
            '35458',
            $provider->floor('35458.1485207464760293448564751702377579632773756221209731837301291644')
        );

        // negative
        // int
        self::assertSame('-35458', $provider->floor(-35458));

        // float
        self::assertSame('-35459', $provider->floor(-35458.0000001));
        self::assertSame('-35459', $provider->floor(-35458.4));
        self::assertSame('-35459', $provider->floor(-35458.5));
        self::assertSame('-35459', $provider->floor(-35458.6));

        // string
        self::assertSame(
            '-35459',
            $provider->floor('-35458.1485207464760293448564751702377579632773756221209731837301291644')
        );
    }

    public function test_round(): void {
        /** @var MathServiceInterface $provider */
        $provider = app(MathServiceInterface::class);

        // positive
        // int
        self::assertSame('35458', $provider->round(35458));

        // float
        self::assertSame('35458', $provider->round('35458.00000000'));
        self::assertSame('35458', $provider->round(35458.0000001));
        self::assertSame('35458', $provider->round(35458.4));
        self::assertSame('35459', $provider->round(35458.5));
        self::assertSame('35459', $provider->round(35458.6));

        // string
        self::assertSame(
            '35458',
            $provider->round('35458.1485207464760293448564751702377579632773756221209731837301291644')
        );

        // negative
        // int
        self::assertSame('-35458', $provider->round(-35458));

        // float
        self::assertSame('-35458', $provider->round(-35458.0000001));
        self::assertSame('-35458', $provider->round(-35458.4));
        self::assertSame('-35459', $provider->round(-35458.5));
        self::assertSame('-35459', $provider->round(-35458.6));

        // string
        self::assertSame(
            '-35458',
            $provider->round('-35458.1485207464760293448564751702377579632773756221209731837301291644')
        );
    }

    /**
     * @return array<string[]>
     */
    public static function invalidProvider(): array {
        return [['.'], ['hello'], ['--121'], ['---121']];
    }
}
