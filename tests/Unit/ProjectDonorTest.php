<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\ProjectDonor;
use App\Models\Donor;
use Exception;
use RuntimeException;

class ProjectDonorTest extends TestCase
{
    private Donor $validDonor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validDonor = Donor::at("European Union");
    }

    public function shouldThrowAndAssert($should, $exceptionType, $assertions)
    {
        try {
            $should->__invoke();
            $this->fail();
        } catch (Exception $exception) {
            $this->assertEquals($exceptionType, get_class($exception));
            $assertions->__invoke($exception);
        }
    }

    public function test_project_donor_can_be_created_with_valid_data()
    {
        $projectDonor = ProjectDonor::at($this->validDonor, 75.5);

        $this->assertInstanceOf(ProjectDonor::class, $projectDonor);
        $this->assertEquals($this->validDonor, $projectDonor->getDonor());
        $this->assertEquals(75.5, $projectDonor->getContribution());
        $this->assertEquals("European Union", $projectDonor->getDonorName());
    }

    public function test_project_donor_with_minimum_contribution()
    {
        $projectDonor = ProjectDonor::at($this->validDonor, 0.0);

        $this->assertEquals(0.0, $projectDonor->getContribution());
    }

    public function test_project_donor_with_maximum_contribution()
    {
        $projectDonor = ProjectDonor::at($this->validDonor, 100.0);

        $this->assertEquals(100.0, $projectDonor->getContribution());
    }

    public function test_project_donor_with_invalid_donor_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectDonor::at("not a donor", 50.0);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProjectDonor::$ERROR_DONOR_INVALID, $exception->getMessage());
            }
        );
    }

    public function test_project_donor_with_non_numeric_contribution_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectDonor::at($this->validDonor, "not a number");
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProjectDonor::$ERROR_CONTRIBUTION_NOT_NUMERIC, $exception->getMessage());
            }
        );
    }

    public function test_project_donor_with_negative_contribution_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectDonor::at($this->validDonor, -10.0);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProjectDonor::$ERROR_CONTRIBUTION_OUT_OF_RANGE, $exception->getMessage());
            }
        );
    }

    public function test_project_donor_with_contribution_over_100_throws_exception()
    {
        $this->shouldThrowAndAssert(
            function () {
                ProjectDonor::at($this->validDonor, 150.0);
            },
            RuntimeException::class,
            function ($exception) {
                $this->assertEquals(ProjectDonor::$ERROR_CONTRIBUTION_OUT_OF_RANGE, $exception->getMessage());
            }
        );
    }

    public function test_project_donor_contribution_can_be_float()
    {
        $projectDonor = ProjectDonor::at($this->validDonor, 33.33);

        $this->assertEquals(33.33, $projectDonor->getContribution());
    }

    public function test_project_donor_contribution_can_be_string_numeric()
    {
        $projectDonor = ProjectDonor::at($this->validDonor, "45.67");

        $this->assertEquals(45.67, $projectDonor->getContribution());
    }

    public function test_project_donor_getters_work_correctly()
    {
        $projectDonor = ProjectDonor::at($this->validDonor, 80.0);

        $this->assertInstanceOf(Donor::class, $projectDonor->getDonor());
        $this->assertEquals("European Union", $projectDonor->getDonorName());
        $this->assertEquals(80.0, $projectDonor->getContribution());
    }
}