<?php

namespace App\Modules\Sdg\Service;

use App\Modules\Sdg\Domain\Sdg;
use App\Modules\Sdg\Repository\SdgRepository;
use RuntimeException;

class SdgService
{
    /**
     * SDG Repository
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
     * Create a new SDG
     * @param string $image
     * @param string $filename
     * @return Sdg
     */
    public function createSdg(string $image, string $filename): Sdg
    {
        if ($this->sdgRepository->exists('filename', trim($filename))) {
            throw new RuntimeException("An SDG with the name already exists: {$filename}");
        }
        $sdg = Sdg::at($image, $filename);
        $this->sdgRepository->save($sdg);
        return $sdg;
    }

    /**
     * Get an SDG by ID
     * @param int $id
     * @return Sdg
     */
    public function getSdgById(int $id): Sdg
    {
        return $this->sdgRepository->findById($id);
    }

    /**
     * Search SDG by filename
     * @param string $filename
     * @return Sdg
     */
    public function findSdgByFilename(string $filename): Sdg
    {
        return $this->sdgRepository->findBy('filename', $filename);
    }

    /**
     * List all SDGs with pagination
     * @param int $perPage Number of records per page (default: 10)
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllSdgs(int $perPage = 10)
    {
        return $this->sdgRepository->paginate($perPage);
    }

    /**
     * Update an existing SDG
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
                throw new RuntimeException("Another SDG with the name already exists: {$filename}");
            }
        } catch (RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'not found')) {
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
