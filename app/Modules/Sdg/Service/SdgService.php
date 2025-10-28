<?php

namespace App\Modules\Sdg\Service;

use App\Modules\Sdg\Domain\Sdg;
use App\Modules\Sdg\Repository\SdgRepository;
use RuntimeException;

class SdgService
{
    /**
     * Repositorio de SDG
     * @var SdgRepository
     */
    private SdgRepository $sdgRepository;

    /**
     * Constructor
     * @param SdgRepository $sdgRepository
     */
    public function __construct(SdgRepository $sdgRepository)
    {
        $this->sdgRepository = $sdgRepository;
    }

    /**
     * Crear un nuevo SDG
     * @param string $image
     * @return Sdg
     */
    public function createSdg(string $image): Sdg
    {
        if ($this->sdgRepository->exists('image', trim($image))) {
            throw new RuntimeException("Ya existe un SDG con la imagen: {$image}");
        }
        $sdg = Sdg::at($image);
        $this->sdgRepository->save($sdg);
        return $sdg;
    }

    /**
     * Obtener un SDG por ID
     * @param int $id
     * @return Sdg
     */
    public function getSdgById(int $id): Sdg
    {
        return $this->sdgRepository->findById($id);
    }

    /**
     * Buscar SDG por imagen
     * @param string $image
     * @return Sdg
     */
    public function findSdgByImage(string $image): Sdg
    {
        return $this->sdgRepository->findBy('image', $image);
    }

    /**
     * Listar todos los SDGs
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSdgs()
    {
        return $this->sdgRepository->getAll();
    }

    /**
     * Actualizar un SDG existente
     * @param int $id
     * @param string $image
     * @return Sdg
     */
    public function updateSdg(int $id, string $image): Sdg
    {
        $sdg = $this->sdgRepository->findById($id);
        try {
            $existing = $this->sdgRepository->findBy('image', trim($image));
            if ($existing && $existing->id !== $id) {
                throw new RuntimeException("Ya existe otro SDG con la imagen: {$image}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'no encontrado')) {
                throw $e;
            }
        }
        $updated = Sdg::at($image);
        $sdg->image = $updated->image;
        $this->sdgRepository->save($sdg);
        return $sdg;
    }

    /**
     * Verificar si existe un SDG por imagen
     * @param string $image
     * @return bool
     */
    public function sdgExists(string $image): bool
    {
        return $this->sdgRepository->exists('image', $image);
    }
}
