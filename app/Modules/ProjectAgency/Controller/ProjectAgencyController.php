<?php

namespace App\Modules\ProjectAgency\Controller;

use App\Http\Resources\AgencyResource;
use App\Http\Resources\ProjectAgencyResource;
use App\Http\Resources\ProjectResource;
use App\Modules\ProjectAgency\Service\ProjectAgencyService;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectAgencyController extends Controller
{
    private ProjectAgencyService $projectAgencyService;

    public function __construct(ProjectAgencyService $projectAgencyService)
    {
        $this->projectAgencyService = $projectAgencyService;
    }

    public function index()
    {
        try {
            $projectAgencies = $this->projectAgencyService->getAllProjectAgencies();

            $projectAgencies->load(["project", "agency"]);

            return ApiResponse::success(
                'Lista de relaciones proyecto-agencia obtenida exitosamente',
                200,
                [
                    'projectAgencies' => ProjectAgencyResource::collection($projectAgencies),
                    'total' => count($projectAgencies)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ApiResponse::error('Error interno del servidor', 500);
        }
    }

    public function showProjectsByAgencyId(int $id)
    {
        try {
            $projects = $this->projectAgencyService->findProjectsByAgencyId($id);

            return ApiResponse::success(
                "Lista de proyectos para la agencia con id {$id}.",
                200,
                [
                    'projects' => ProjectResource::collection($projects),
                    'total' => count($projects)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    public function showProjectsByAgencyName(string $name)
    {
        try {
            $projects = $this->projectAgencyService->findProjectsByAgencyName($name);

            return ApiResponse::success(
                "Lista de proyectos asociados a la agencia '{$name}'.",
                200,
                [
                    'projects' => ProjectResource::collection($projects),
                    'total' => count($projects)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    public function showAgenciesByProjectId(int $id)
    {
        try {
            $agencies = $this->projectAgencyService->findAgenciesByProjectId($id);

            return ApiResponse::success(
                "Lista de agencias para el proyecto con id {$id}.",
                200,
                [
                    'agencies' => AgencyResource::collection($agencies),
                    'total' => count($agencies)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    public function showAgenciesByProjectName(string $name)
    {
        try {
            $agencies = $this->projectAgencyService->findAgenciesByProjectName($name);

            return ApiResponse::success(
                "Lista de agencias asociadas al proyecto '{$name}'.",
                200,
                [
                    'agencies' => AgencyResource::collection($agencies),
                    'total' => count($agencies)
                ]
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    public function createProjectAgency(Request $request)
    {
        try {
            $projectId = $request->input('project_id');
            $agencyId = $request->input('agency_id');
            if (!$projectId || !$agencyId) throw new \RuntimeException("Los campos project_id y agency_id son obligatorios.");

            $projectAgency = $this->projectAgencyService->createProjectAgency($projectId, $agencyId);
            $projectAgency->load(['project', 'agency']);

            return ApiResponse::success(
                "Relación creada correctamente entre el proyecto {$projectId} y la agencia {$agencyId}.",
                201,
                new ProjectAgencyResource($projectAgency)
            );

        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }

    public function deleteProjectAgency(Request $request)
    {
        try {
            $projectAgencyId = $request->input('project_agency_id');
            if (!$projectAgencyId) throw new \RuntimeException("El campo project_agency_id es obligatorio.");
            $this->projectAgencyService->deleteProjectAgency($projectAgencyId);

            return ApiResponse::success(
                "La relación project-agency con id {$projectAgencyId} fue eliminada correctamente.",
                200
            );

        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error("Error interno del servidor.", 500);
        }
    }
}