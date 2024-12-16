<?php

declare(strict_types=1);

namespace App\UI\Pet;

use App\Exception\ApiHttpError;
use App\Model\Enum\Pet\PetStatusEnum;
use App\Model\Pet\Pet;
use App\Model\PetFacade;
use App\Utils\Form as FormUtils;
use Nette;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;

final class PetPresenter extends Nette\Application\UI\Presenter
{
	#[Persistent]
	public string $backlink = '';

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

	public function renderCreate(): void
	{
		$this->restoreRequest($this->backlink);
	}

	public function renderShow(int $id): void
	{
		try {
			$pet = $this->petFacade->getOne($id);
		} catch (ApiHttpError) {
			$this->error('Stránka nebola nájdená');
		}

		$this->template->pet = $pet;
	}

	public function renderEdit(int $id): void
	{
		try {
			$pet = $this->petFacade->getOne($id);
		} catch (ApiHttpError) {
			$this->error('Stránka nebola nájdená');
		}

		$this->getComponent('petForm')
			->setDefaults($pet);

		$this->restoreRequest($this->backlink);

		$this->template->pet = $pet;
	}

	public function renderDelete(int $id): void
	{
		try {
			$pet = $this->petFacade->getOne($id);
		} catch (ApiHttpError) {
			$this->error('Stránka nebola nájdená');
		}

		$this->template->pet = $pet;
	}

	public function actionDeleteConfirmation(int $id): void
	{
		try {
			$this->petFacade->deletePet($id);

			$this->flashMessage('Zvieratko úspešne vymazané.', 'success');
		} catch (ApiHttpError) {
			$this->flashMessage('Zvieratko sa nepodarilo vymazať.', 'danger');
		}

		$this->redirect(':');
	}

	protected function createComponentPetForm(): Form
	{
		$form = new Form();

		$form->onRender[] = function ($form) {
			FormUtils::makeBootstrap5($form);
		};

		if (!$this->getParameter('id')) {
			$form->addInteger('id', 'ID:')
				->setRequired();
		}

		$form->addText('name', 'Meno:')
			->setRequired();

		$category = $form->addContainer('category');

		$category->addText('name', 'Kategória:')
			->setNullable();

		if ($this->getParameter('id')) {
			$form->addRadioList('status', 'Stav:', PetStatusEnum::TITLES)
				->setRequired();
		}

		if ($this->getParameter('id')) {
			$form->addHidden('image')
				->setNullable();
		}

		$form->addCheckbox('uploadImage', $this->getParameter('id') ? 'Aktualizovať obrázok' : 'Pridať obrázok')
			->setOmitted();

		$form->addUpload('newImage', 'Obrázok:')
			->addRule(Form::Image, 'Obrázok musí být JPEG, PNG, GIF, WebP alebo AVIF.')
			->setOmitted();

		$form->addSubmit('send', 'Uložit');

		$form->onSuccess[] = $this->petFormSucceeded(...);

		return $form;
	}

	private function petFormSucceeded(Form $form, Pet $pet): void
	{
		if (!$pet->category->name) {
			$pet->category = null;
		}

		if ($uploadImage = $form['uploadImage']->getValue()) {
			$pet->image = null;
		}

		$uploadImageCallback = function(int $petId) use ($form, $uploadImage) {
			if ($uploadImage && ($file = $form['newImage']->getValue())->hasFile()) {
				try {
					$this->petFacade->uploadImage($petId, $file->getContents());
				} catch (ApiHttpError) {
					$this->flashMessage('Obrázok sa nepodarilo nahrať.', 'danger');
				}
			}
		};

		if ($id = (int) $this->getParameter('id')) {
			$pet->id = $id;

			try {
				$pet = $this->petFacade->updatePet($pet);

				$this->flashMessage('Zvieratko úspešne upravené.', 'success');

				$uploadImageCallback($pet->id);

				$this->redirect(':show', [$pet->id]);
			} catch (ApiHttpError) {
				$this->flashMessage('Zvieratko sa nepodarilo upraviť.', 'danger');

				$this->redirect('this', ['backlink' => $this->storeRequest()]);
			}
		} else {
			$pet->status = PetStatusEnum::AVAILABLE();

			try {
				$pet = $this->petFacade->createPet($pet);

				$this->flashMessage('Zvieratko úspešne vytvorené.', 'success');

				$uploadImageCallback($pet->id);

				$this->redirect(':show', [$pet->id]);
			} catch (ApiHttpError) {
				$this->flashMessage('Zvieratko sa nepodarilo vytvoriť.', 'danger');

				$this->redirect('this', ['backlink' => $this->storeRequest()]);
			}
		}
	}
}
