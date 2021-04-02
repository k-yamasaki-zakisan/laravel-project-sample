<?php

namespace App\Observers;

use App\Models\Person;
use App\Services\RandomValueGenerator;

class PersonObserver
{
	public function creating(Person $person) {
		$person->link_key = RandomValueGenerator::generateLinkKey();
		$person->full_name = $person->buildFullName();
		$data = $person->makeVisible('password')->toArray();

		validator($data, $person->creatingRules($data))->validate();
	}

    public function created(Person $person)
    {
        //
    }

	public function updating(Person $person) {
		$person->full_name = $person->buildFullName();
		$data = $person->makeVisible('password')->toArray();

		validator($data, $person->updatingRules($data))->validate();
	}

    public function updated(Person $person)
    {
        //
    }

    public function deleted(Person $person)
    {
        //
    }

    public function restored(Person $person)
    {
        //
    }

    public function forceDeleted(Person $person)
    {
        //
    }
}