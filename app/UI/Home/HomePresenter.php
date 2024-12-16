<?php

declare(strict_types=1);

namespace App\UI\Home;

use App\Exception\ApiHttpError;
use App\Model\PetFacade;
use Nette;

final class HomePresenter extends Nette\Application\UI\Presenter
{
	public function __construct(private PetFacade $petFacade)
	{
	}

	public function renderDefault(): void
	{
		try {
			$this->template->availablePets = $this->petFacade->getAvailablePets();
			$this->template->pendingPets = $this->petFacade->getPendingPets();
			$this->template->soldPets = $this->petFacade->getSoldPets();
		} catch (ApiHttpError) {
			$this->template->availablePets = [];
			$this->template->pendingPets = [];
			$this->template->soldPets = [];
		}
	}
}
