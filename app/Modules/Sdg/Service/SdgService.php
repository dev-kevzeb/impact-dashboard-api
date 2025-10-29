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
     * @param string $filename
     * @return Sdg
     */
    public function createSdg(string $image, string $filename): Sdg
    {
        if ($this->sdgRepository->exists('filename', trim($filename))) {
            throw new RuntimeException("Ya existe un SDG con el nombre: {$filename}");
        }
        $sdg = Sdg::at($image, $filename);
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
     * Buscar SDG por filename
     * @param string $filename
     * @return Sdg
     */
    public function findSdgByFilename(string $filename): Sdg
    {
        return $this->sdgRepository->findBy('filename', $filename);
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
     * @param string $filename
     * @return Sdg
     */
    public function updateSdg(int $id, string $image, string $filename): Sdg
    {
        $sdg = $this->sdgRepository->findById($id);
        try {
            $existing = $this->sdgRepository->findBy('filename', trim($filename));
            if ($existing && $existing->id !== $id) {
                throw new RuntimeException("Ya existe otro SDG con el nombre: {$filename}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'no encontrado')) {
                throw $e;
            }
        }
        $updated = Sdg::at($image, $filename);
        $sdg->image = $updated->image;
        $sdg->filename = $updated->filename;
        $this->sdgRepository->save($sdg);
        return $sdg;
    }

    /**
     * Verificar si existe un SDG por filename
     * @param string $filename
     * @return bool
     */
    public function sdgExists(string $filename): bool
    {
        return $this->sdgRepository->exists('filename', $filename);
    }
}
