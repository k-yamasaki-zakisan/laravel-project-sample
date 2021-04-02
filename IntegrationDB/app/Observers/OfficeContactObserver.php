<?php

namespace App\Observers;

use App\Models\OfficeContact;
use App\Services\RandomValueGenerator;

class OfficeContactObserver
{
	public function creating(OfficeContact $OfficeContact) {
		$OfficeContact->link_key = RandomValueGenerator::generateLinkKey();
		$data = $OfficeContact->toArray();

		validator($data, $OfficeContact->creatingRules($data))->validate();
	}

	public function updating(OfficeContact $OfficeContact) {
		$data = $OfficeContact->toArray();

        validator($data, $OfficeContact->updatingRules($data))->validate();
	}
}