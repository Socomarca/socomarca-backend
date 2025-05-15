<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is \PHPUnit\Framework\TestCase. Of course, you may
| need to change it using the "uses()" function to bind a different classes such as
| \Illuminate\Foundation\Testing\TestCase to communicate with your Laravel application.
|
*/

uses(Tests\TestCase::class)->in('Feature');
//uses(Tests\TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions.
| Pest provides a beautiful API for doing this, however, you may prefer
| to use the traditional PHPUnit assertions sometimes.
|
| Here you can register any custom expectations you wish to use:
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to
| your project that you don't want to repeat in every file. Here you can also expose helpers
| to be used globally across all your tests.
|
*/

// function something() {
//     // ...
// }
