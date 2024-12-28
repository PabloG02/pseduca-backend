<?php

namespace Tests\Controllers;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../loadconfig.php';

use App\Controllers\AcademicProgramController;
use App\Services\AcademicProgramService;
use App\Entities\AcademicProgram;
use PHPUnit\Framework\TestCase;

class AcademicProgramControllerTest extends TestCase
{
    private AcademicProgramController $controller;
    private $academicProgramServiceMock;

    protected function setUp(): void
    {   
        
        // Configuración de mock para AcademicProgramService
        $this->academicProgramServiceMock = $this->createMock(AcademicProgramService::class);
        
        // Inicialización del controlador con el servicio mockeado
        $this->controller = $this->getMockBuilder(AcademicProgramController::class)
            ->setConstructorArgs([$this->academicProgramServiceMock])
            ->onlyMethods(['hasRole', 'createFilterFromRequest', 'saveImageFile', 'deleteImageFile'])
            ->getMock();

        // Mock de 'hasRole' o cualquier otro método necesario
        $this->controller->method('hasRole')
            ->willReturn(true);

            
    }
        

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
    }

    private function setRole($role): void
    {
        $_SESSION['role'] = $role;
    }


    public function testCreateMissingFields(): void
    {
        $_POST = [
            'qualification_level' => 'Bachelor',
            'available_slots' => 30,
            'teaching_type' => 'Online',
            'offering_frequency' => 'Annual',
            'duration_ects' => 180,
            'location' => 'Test Location',
        ];

        // Simulación de campos faltantes
        $this->controller->create();

        $this->expectOutputString(json_encode(['error' => 'Invalid or missing required fields.']));
        $this->assertSame(400, http_response_code());
    }

    public function testCreateSuccess(): void
    {
        $_POST = [
            'name' => 'Test Program',
            'qualification_level' => 'Master',
            'description' => 'Description of the master',
            'available_slots' => 30,
            'teaching_type' => 'Online',
            'offering_frequency' => 'Annual',
            'duration_ects' => 180,
            'location' => 'Test Location',
        ];

        // Mock de la creación exitosa del programa académico
        $this->academicProgramServiceMock
            ->expects($this->once())
            ->method('create')
            ->willReturn(1); // Retornamos un ID ficticio para simular la creación exitosa

        $this->controller->create();

        $this->expectOutputString(json_encode(['message' => 'Academic program created successfully.', 'id' => 1]));
        $this->assertSame(201, http_response_code());
    }

    public function testUpdateInvalidId(): void
    {
        $_POST = ['id' => 'invalid']; // ID no válido

        $this->controller->update();

        $this->expectOutputString(json_encode(['error' => 'Invalid academic program ID.']));
        $this->assertSame(400, http_response_code());
    }

    public function testUpdateNotFound(): void
    {
        $_POST = ['id' => 1]; // ID válido pero el programa no existe

        $this->academicProgramServiceMock
            ->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn(null); // El programa no se encuentra en la base de datos

        $this->controller->update();

        $this->expectOutputString(json_encode(['error' => 'Academic program not found.']));
        $this->assertSame(404, http_response_code());
    }

    public function testUpdateSuccess(): void
    {
        $_POST = ['id' => 1, 'name' => 'Updated Name']; // Datos de actualización

        $program = new AcademicProgram(1, 'Old Name', 'Master', "", "", "", 30, 'Online', 'Annual', 180, 'Location', "");

        // Mock para recuperar el programa y actualizarlo
        $this->academicProgramServiceMock
            ->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($program);

        $this->academicProgramServiceMock
            ->expects($this->once())
            ->method('update')
            ->with($program);

        $this->controller->update();

        $this->expectOutputString(json_encode(['message' => 'Academic program updated successfully.']));
        $this->assertSame(200, http_response_code());
    }

    public function testDeleteNotFound(): void
    {
        $_POST = ['id' => 1]; // ID de programa no encontrado

        $this->academicProgramServiceMock
            ->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn(null); // El programa no se encuentra en la base de datos

        $this->controller->delete();

        $this->expectOutputString(json_encode(['error' => 'Academic program not found.']));
        $this->assertSame(404, http_response_code());
    }

    public function testDeleteSuccess(): void
    {
        $_POST = ['id' => 1]; // ID de programa válido

        $program = new AcademicProgram(1, 'Program Name', 'Master', "", "", "", 30, 'Online', 'Annual', 180, 'Location', "");

        // Mock para recuperar el programa y eliminarlo
        $this->academicProgramServiceMock
            ->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($program);

        $this->academicProgramServiceMock
            ->expects($this->once())
            ->method('delete')
            ->with(1);

        $this->controller->delete();

        $this->expectOutputString(json_encode(['message' => 'Academic program deleted successfully.']));
        $this->assertSame(200, http_response_code());
    }

    public function testGetNotFound(): void
    {
        $_POST = ['id' => 1]; // ID de programa no encontrado

        $this->academicProgramServiceMock
            ->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn(null); // El programa no se encuentra en la base de datos

        $this->controller->get();

        $this->expectOutputString(json_encode(['error' => 'Academic program not found.']));
        $this->assertSame(404, http_response_code());
    }

    public function testGetSuccess(): void
    {
        $_POST = ['id' => 1]; // ID de programa válido

        $program = new AcademicProgram(1, 'Program Name', 'Master', "", "", "", 30, 'Online', 'Annual', 180, 'Location', "");

        // Mock para recuperar el programa
        $this->academicProgramServiceMock
            ->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($program);

        $this->controller->get();

        $this->expectOutputString(json_encode($program));
        $this->assertSame(200, http_response_code());
    }
}
