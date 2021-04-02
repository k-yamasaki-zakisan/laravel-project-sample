<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
		Validator::extend('date_multi_format', function($attribute, $value, $formats) {
			// iterate through all formats
			foreach($formats as $format) {
				// parse date with current format
				$parsed = date_parse_from_format($format, $value);

				// if value matches given format return true=validation succeeded 
				if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
					return true;
				}
			}

			// value did not match any of the provided formats, so return false=validation failed
			return false;
		});
		// 厳密なin比較
		Validator::extend('strict_in', function($attribute, $value, $parameters) {
			return in_array($value, $parameters, true);
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		// add by.ando
		$this->app->bind(
			\App\Repositories\ClientRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientRepository::class
		);
		$this->app->bind(
			\App\Repositories\ClientUserRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientUserRepository::class
		);
		$this->app->bind(
			\App\Repositories\ClientEmployeeRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientEmployeeRepository::class
		);
		//@YuKaneko
		$this->app->bind(
			\App\Repositories\EnvRepositoryInterface::class,
			\App\Infrastracture\Repositories\EnvRepository::class
		);
		$this->app->bind(
			\App\Repositories\PrefectureRepositoryInterface::class,
			\App\Infrastracture\Repositories\PrefectureRepository::class
		);
		$this->app->bind(
			\App\Repositories\CompanyRepositoryInterface::class,
			\App\Infrastracture\Repositories\CompanyRepository::class
		);
		$this->app->bind(
			\App\Repositories\ClientAreaRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientAreaRepository::class
		);
		$this->app->bind(
			\App\Repositories\CompanyGroupRepositoryInterface::class,
			\App\Infrastracture\Repositories\CompanyGroupRepository::class
		);
		$this->app->bind(
			\App\Repositories\CompanyDepartmentRepositoryInterface::class,
			\App\Infrastracture\Repositories\CompanyDepartmentRepository::class
		);
		$this->app->bind(
			\App\Repositories\CompanyStaffRepositoryInterface::class,
			\App\Infrastracture\Repositories\CompanyStaffRepository::class
		);
		$this->app->bind(
			\App\Repositories\QuoteStatusRepositoryInterface::class,
			\App\Infrastracture\Repositories\QuoteStatusRepository::class
		);
		$this->app->bind(
			\App\Repositories\QuoteRepositoryInterface::class,
			\App\Infrastracture\Repositories\QuoteRepository::class
		);
		$this->app->bind(
			\App\Repositories\QuoteDetailRepositoryInterface::class,
			\App\Infrastracture\Repositories\QuoteDetailRepository::class
		);
		$this->app->bind(
			\App\Repositories\ClientSettingRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientSettingRepository::class
		);
		$this->app->bind(
			\App\Repositories\EmploymentTypeRepositoryInterface::class,
			\App\Infrastracture\Repositories\EmploymentTypeRepository::class
		);
		$this->app->bind(
			\App\Repositories\ClientGroupRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientGroupRepository::class
		);
		$this->app->bind(
			\App\Repositories\ClientEmployeeTrcdSettingRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientEmployeeTrcdSettingRepository::class
		);
		$this->app->bind(
			\App\Repositories\ClientEmployeeOccupationRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientEmployeeOccupationRepository::class
		);
		$this->app->bind(
			\App\Repositories\ClientBranchRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientBranchRepository::class
		);
		$this->app->bind(
			\App\Repositories\LocationRepositoryInterface::class,
			\App\Infrastracture\Repositories\LocationRepository::class
		);
		$this->app->bind(
			\App\Repositories\RouteRepositoryInterface::class,
			\App\Infrastracture\Repositories\RouteRepository::class
		);
		// TRCD --------------------------------------------------
		$this->app->bind(
			\App\Repositories\Trcd\PaidHolidayRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\PaidHolidayRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\AttendanceRequestRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\AttendanceRequestRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\AttendancePaidHolidayRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\AttendancePaidHolidayRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\TrcdTerminalRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\TrcdTerminalRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\AttendanceCsvExportSettingRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\AttendanceCsvExportSettingRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\AttendanceCsvExportSettingDetailRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\AttendanceCsvExportSettingDetailRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\AttendanceCsvExportItemRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\AttendanceCsvExportItemRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\ClientNoteTypeRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\ClientNoteTypeRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\AttendanceNoteRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\AttendanceNoteRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\ExpenseCsvExportSettingRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\ExpenseCsvExportSettingRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\ExpenseCsvExportSettingDetailRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\ExpenseCsvExportSettingDetailRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\ExpenseCsvExportItemRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\ExpenseCsvExportItemRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\TemporaryPaymentCsvExportSettingRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\TemporaryPaymentCsvExportSettingRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\TemporaryPaymentCsvExportSettingDetailRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\TemporaryPaymentCsvExportSettingDetailRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\TemporaryPaymentCsvExportItemRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\TemporaryPaymentCsvExportItemRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\ExpenseGroupRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\ExpenseGroupRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\ExpenseGroupSettingRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\ExpenseGroupSettingRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\ExpenseNotificationDestinationRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\ExpenseNotificationDestinationRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\TemporaryPaymentSummaryCandidateRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\TemporaryPaymentSummaryCandidateRepository::class
		);
		//@sekiya
		$this->app->bind(
			\App\Repositories\VehicleRepositoryInterface::class,
			\App\Infrastracture\Repositories\VehicleRepository::class
		);
		$this->app->bind(
			\App\Repositories\VehicleOverheadExpensesRepositoryInterface::class,
			\App\Infrastracture\Repositories\VehicleOverheadExpensesRepository::class
		);
		$this->app->bind(
			\App\Repositories\TariffRepositoryInterface::class,
			\App\Infrastracture\Repositories\TariffRepository::class
		);
		$this->app->bind(
			\App\Repositories\BreakTypeRepositoryInterface::class,
			\App\Infrastracture\Repositories\BreakTypeRepository::class
		);
		$this->app->bind(
			\App\Repositories\AttendancePatternRepositoryInterface::class,
			\App\Infrastracture\Repositories\AttendancePatternRepository::class
		);
		$this->app->bind(
			\App\Repositories\AttendanceSpecialTypeRepositoryInterface::class,
			\App\Infrastracture\Repositories\AttendanceSpecialTypeRepository::class
		);
		$this->app->bind(
			\App\Repositories\CsvRepositoryInterface::class,
			\App\Infrastracture\Repositories\ClientEmployeeCsvRepository::class
		);
		//@add terada
		$this->app->bind(
			\App\Repositories\ApplicationVersionRepositoryInterface::class,
			\App\Infrastracture\Repositories\ApplicationVersionRepository::class
		);
		$this->app->bind(
                        \App\Repositories\Trcd\ExpenseImageRepositoryInterface::class,
                        \App\Infrastracture\Repositories\Trcd\ExpenseImageRepository::class
                );
		$this->app->bind(
                        \App\Repositories\Trcd\ExpenseSummaryRequestRepositoryInterface::class,
                        \App\Infrastracture\Repositories\Trcd\ExpenseSummaryRequestRepository::class
                );
		$this->app->bind(
                        \App\Repositories\Trcd\ExpenseHeaderRequestRepositoryInterface::class,
                        \App\Infrastracture\Repositories\Trcd\ExpenseHeaderRequestRepository::class
                );
		$this->app->bind(
                        \App\Repositories\Trcd\ExpenseSummaryRepositoryInterface::class,
                        \App\Infrastracture\Repositories\Trcd\ExpenseSummaryRepository::class
                );
                $this->app->bind(
                        \App\Repositories\Trcd\ExpenseHeaderRepositoryInterface::class,
                        \App\Infrastracture\Repositories\Trcd\ExpenseHeaderRepository::class
                );
		$this->app->bind(
                        \App\Repositories\Trcd\AccountTitleRepositoryInterface::class,
                        \App\Infrastracture\Repositories\Trcd\AccountTitleRepository::class
                );
		$this->app->bind(
                        \App\Repositories\Trcd\AccountTitleClientRepositoryInterface::class,
                        \App\Infrastracture\Repositories\Trcd\AccountTitleClientRepository::class
                );
		$this->app->bind(
			\App\Repositories\MailDeliveryTaskRepositoryInterface::class,
			\App\Infrastracture\Repositories\MailDeliveryTaskRepository::class
		);
		$this->app->bind(
			\App\Repositories\MailDeliveryHistoryRepositoryInterface::class,
			\App\Infrastracture\Repositories\MailDeliveryHistoryRepository::class
		);
		$this->app->bind(
			\App\Repositories\Trcd\ExpenseImagesDirectoryManipulationTaskRepositoryInterface::class,
			\App\Infrastracture\Repositories\Trcd\ExpenseImagesDirectoryManipulationTaskRepository::class
		);
		$this->app->bind(
    		\App\Repositories\Trcd\BalanceThresholdRepositoryInterface::class,
    		\App\Infrastracture\Repositories\Trcd\BalanceThresholdRepository::class
		);
        $this->app->bind(
            \App\Repositories\Trcd\TrcdTerminalNotificationSettingRepositoryInterface::class,
            \App\Infrastracture\Repositories\Trcd\TrcdTerminalNotificationSettingRepository::class
		);

		$this->app->bind(
            \App\Repositories\Trcd\TrcdTerminalNotificationDestinationRepositoryInterface::class,
            \App\Infrastracture\Repositories\Trcd\TrcdTerminalNotificationDestinationRepository::class
        );
	}
}
