<?php

declare(strict_types=1);

namespace App\Api\Controller\V1;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Exception\Api\ValidationException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Apitte\Core\Schema\EndpointParameter;
use App\Api\Exception\EntityNotFoundException;
use App\Api\Negotiation\Http\MappingEntity;
use App\Model\Enum\Pet\PetStatusEnum;
use App\Model\Pet\Pet as Entity;
use App\Repository\Pet\PetRepository as Repository;
use App\Utils\FileInfo;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Apitte\Path('/pet')]
#[Apitte\Tag('pet')]
class PetController extends BaseV1Controller
{
	public function __construct(private Repository $repository, private SerializerInterface $serializer)
	{
	}

	#[Apitte\OpenApi('
		summary: Finds Pets by status
		description: Finds Pets by status
	')]
	#[Apitte\Path('/findByStatus')]
	#[Apitte\Method('GET')]
	#[Apitte\RequestParameter(
		name: 'status',
		type: EndpointParameter::TYPE_ENUM,
		in: EndpointParameter::IN_QUERY,
		description: 'Status values that need to be considered for filter',
		enum: PetStatusEnum::VALUES,
	)]
	#[Apitte\Response(code: ApiResponse::S200_OK.'', description: 'Successful operation')]
	#[Apitte\Response(code: ApiResponse::S400_BAD_REQUEST.'', description: 'Invalid status value')]
	public function findByStatus(ApiRequest $request): array
	{
		return $this->repository->findBy('status', $request->getParameter('status'));
	}

	// TODO: findByTags

	#[Apitte\OpenApi('
		summary: Add a new pet to the store
		description: Add a new pet to the store
	')]
	#[Apitte\Path('/')]
	#[Apitte\Method('POST')]
	#[Apitte\RequestBody(entity: Entity::class, required: true, description: 'Create a new pet in the store')]
	#[Apitte\Response(code: ApiResponse::S201_CREATED.'', description: 'Successful operation', entity: Entity::class)]
	#[Apitte\Response(code: ApiResponse::S400_BAD_REQUEST.'', description: 'Invalid input')]
	#[Apitte\Response(code: ApiResponse::S409_CONFLICT.'', description: 'Pet creation failed')]
	#[Apitte\Response(code: ApiResponse::S422_UNPROCESSABLE_ENTITY.'', description: 'Invalid input')]
	public function new(ApiRequest $request, ApiResponse $response): ApiResponse
	{
		if (!$entity = $this->repository->add($request->getEntity())) {
			throw ClientErrorException::create()
				->withMessage('Pet creation failed')
				->withCode(ApiResponse::S409_CONFLICT);
		}

		return $response
			->withStatus(ApiResponse::S201_CREATED)
			->withEntity(MappingEntity::from($entity)->setSerializer($this->serializer));
	}

	#[Apitte\OpenApi('
		summary: Find pet by ID
		description: Returns a single pet
	')]
	#[Apitte\Path('/{petId}')]
	#[Apitte\Method('GET')]
	#[Apitte\RequestParameter(
		name: 'petId',
		type: EndpointParameter::TYPE_INTEGER,
		description: 'ID of pet to return',
	)]
	#[Apitte\Response(code: ApiResponse::S200_OK.'', description: 'Successful operation', entity: Entity::class)]
	#[Apitte\Response(code: ApiResponse::S400_BAD_REQUEST.'', description: 'Invalid ID supplied')]
	#[Apitte\Response(code: ApiResponse::S404_NOT_FOUND.'', description: 'Pet not found')]
	public function show(ApiRequest $request): Entity
	{
		if (!$entity = $this->repository->findOneBy('id', $request->getParameter('petId'))) {
			throw new EntityNotFoundException('Pet not found');
		}

		return $entity;
	}

	#[Apitte\OpenApi('
		summary: Updates a pet in the store with form data
	')]
	#[Apitte\Path('/{petId}')]
	#[Apitte\Method('POST')]
	#[Apitte\RequestParameter(
		name: 'petId',
		type: EndpointParameter::TYPE_INTEGER,
		description: 'ID of pet that needs to be updated',
	)]
	#[Apitte\RequestParameter(
		name: 'name',
		type: EndpointParameter::TYPE_STRING,
		in: EndpointParameter::IN_QUERY,
		required: false,
		description: 'Name of pet that needs to be updated',
	)]
	#[Apitte\RequestParameter(
		name: 'status',
		type: EndpointParameter::TYPE_ENUM,
		in: EndpointParameter::IN_QUERY,
		required: false,
		description: 'Status of pet that needs to be updated',
		enum: PetStatusEnum::VALUES,
	)]
	#[Apitte\Response(code: ApiResponse::S200_OK.'', description: 'Successful operation', entity: Entity::class)]
	#[Apitte\Response(code: ApiResponse::S400_BAD_REQUEST.'', description: 'Invalid ID or status value supplied')]
	#[Apitte\Response(code: ApiResponse::S404_NOT_FOUND.'', description: 'Pet not found')]
	#[Apitte\Response(code: ApiResponse::S422_UNPROCESSABLE_ENTITY.'', description: 'Invalid input')]
	public function edit(ApiRequest $request, ApiResponse $response): Entity
	{
		$data = [
			'name' => $request->getParameter('name'),
			'status' => $request->getParameter('status'),
		];

		if (!$entity = $this->repository->update($request->getParameter('petId'), $data)) {
			throw new EntityNotFoundException('Pet not found');
		}

		return $entity;
	}

	#[Apitte\OpenApi('
		summary: Deletes a pet
	')]
	#[Apitte\Path('/{petId}')]
	#[Apitte\Method('DELETE')]
	#[Apitte\RequestParameter(
		name: 'petId',
		type: EndpointParameter::TYPE_INTEGER,
		description: 'Pet id to delete',
	)]
	// TODO:
	#[Apitte\RequestParameter(
		name: 'api_key',
		type: EndpointParameter::TYPE_STRING,
		in: EndpointParameter::IN_HEADER,
		required: false,
	)]
	#[Apitte\Response(code: ApiResponse::S204_NO_CONTENT.'', description: 'Pet was deleted')]
	#[Apitte\Response(code: ApiResponse::S404_NOT_FOUND.'', description: 'Pet not found')]
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse
	{
		if (!$this->repository->remove($request->getParameter('petId'))) {
			throw new EntityNotFoundException('Pet not found');
		}

		return $response
			->withStatus(ApiResponse::S204_NO_CONTENT);
	}

	#[Apitte\OpenApi('
		summary: Uploads an image
		requestBody:
			content:
				application/octet-stream:
					schema:
						type: string
						format: binary
	')]
	#[Apitte\Path('/{petId}/uploadImage')]
	#[Apitte\Method('POST')]
	#[Apitte\RequestParameter(
		name: 'petId',
		type: EndpointParameter::TYPE_INTEGER,
		description: 'ID of pet to update',
	)]
	// TODO:
	#[Apitte\RequestParameter(
		name: 'additionalMetadata',
		type: EndpointParameter::TYPE_STRING,
		in: EndpointParameter::IN_QUERY,
		required: false,
		description: 'Additional Metadata',
	)]
	#[Apitte\RequestBody(required: true)]
	#[Apitte\Response(code: ApiResponse::S200_OK.'', description: 'Successful operation')]
	#[Apitte\Response(code: ApiResponse::S400_BAD_REQUEST.'', description: 'Invalid image type')]
	#[Apitte\Response(code: ApiResponse::S404_NOT_FOUND.'', description: 'Pet not found')]
	#[Apitte\Response(code: ApiResponse::S422_UNPROCESSABLE_ENTITY.'', description: 'Invalid input')]
	public function uploadImage(ApiRequest $request, ApiResponse $response): Entity
	{
		if (!$imageContent = $request->getContents()) {
			throw ValidationException::create()
				->withFields(['file' => ['No image uploaded']]);
		}

		if (!$entity = $this->repository->findOneBy('id', $request->getParameter('petId'))) {
			throw new EntityNotFoundException('Pet not found');
		}

		if (!$fileExtension = FileInfo::getSuggestedExtensionFromString($imageContent)) {
			throw ClientErrorException::create()
				->withMessage('Invalid image type')
				->withCode(ApiResponse::S400_BAD_REQUEST);
		}

		$entity->image = base64_encode($imageContent);

		if (!$entity = $this->repository->replace($entity)) {
			throw new EntityNotFoundException('Pet not found');
		}

		return $entity;
	}

	#[Apitte\OpenApi('
		summary: Update an existing pet
		description: Update an existing pet by Id
	')]
	#[Apitte\Path('/')]
	#[Apitte\Method('PUT')]
	#[Apitte\RequestBody(entity: Entity::class, required: true, description: 'Update an existent pet in the store')]
	#[Apitte\Response(code: ApiResponse::S200_OK.'', description: 'Successful operation', entity: Entity::class)]
	#[Apitte\Response(code: ApiResponse::S400_BAD_REQUEST.'', description: 'Invalid input')]
	#[Apitte\Response(code: ApiResponse::S404_NOT_FOUND.'', description: 'Pet not found')]
	#[Apitte\Response(code: ApiResponse::S422_UNPROCESSABLE_ENTITY.'', description: 'Invalid input')]
	public function replace(ApiRequest $request, ApiResponse $response): Entity
	{
		if (!$entity = $this->repository->findOneBy('id', ($newEntity = $request->getEntity())->getPrimaryKey())) {
			throw new EntityNotFoundException('Pet not found');
		}

		$newEntity = $this->serializer->denormalize($newEntity, Entity::class, null, [
			AbstractNormalizer::GROUPS => ['api:input'],
			AbstractNormalizer::OBJECT_TO_POPULATE => $entity,
		]);

		if (!$entity = $this->repository->replace($newEntity)) {
			throw new EntityNotFoundException('Pet not found');
		}

		return $entity;
	}
}
