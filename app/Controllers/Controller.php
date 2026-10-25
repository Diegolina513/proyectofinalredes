<?php
namespace App\Controllers;
use PDOException;

abstract class Controller {
    protected string $modulo;
    protected string $singular;

    public function __construct(protected object $modelo) {}

    abstract protected function normalizar(array $entrada): array;
    abstract protected function validar(array $datos, ?int $id): array;

    protected function texto(array $entrada, string $campo): string {
        return is_string($entrada[$campo] ?? null) ? trim($entrada[$campo]) : '';
    }

    protected function validarTexto(string $valor, string $campo, int $maximo, bool $obligatorio = false): array {
        if ($obligatorio && $valor === '') {
            return ["El campo $campo es obligatorio."];
        }
        $longitud = preg_match_all('/./us', $valor);
        if ($longitud === false || $longitud > $maximo) {
            return ["El campo $campo admite hasta $maximo caracteres."];
        }
        return [];
    }

    protected function datosVista(): array {
        return [];
    }

    protected function vista(string $archivo, array $variables = []): void {
        extract($variables);
        $modulo = $this->modulo;
        $singular = $this->singular;
        require __DIR__ . '/../Views/layout/encabezado.php';
        require __DIR__ . '/../Views/' . $modulo . '/' . $archivo . '.php';
        require __DIR__ . '/../Views/layout/pie.php';
    }

    private function obtenerId(): int {
        $valor = $_SERVER['REQUEST_METHOD'] === 'POST'
            ? ($_POST['id'] ?? $_GET['id'] ?? null) : ($_GET['id'] ?? null);
        $id = \idValido($valor);
        if (!$id) {
            http_response_code(400);
            exit('Identificador inválido.');
        }
        return $id;
    }

    private function buscarRegistro(int $id): array {
        $datos = $this->modelo->buscar($id);
        if (!$datos) {
            http_response_code(404);
            exit('Registro no encontrado.');
        }
        return $datos;
    }

    public function index(): void {
        $this->vista('index', ['filas' => $this->modelo->listar()]);
    }

    public function ver(): void {
        $id = $this->obtenerId();
        $this->vista('ver', ['datos' => $this->buscarRegistro($id)]);
    }

    public function crear(): void {
        $this->formulario(null, []);
    }

    public function editar(): void {
        $id = $this->obtenerId();
        $this->formulario($id, $this->buscarRegistro($id));
    }

    private function formulario(?int $id, array $datos): void {
        $errores = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->normalizar($_POST);
            $errores = $this->validar($datos, $id);
            if (!$errores) {
                try {
                    if ($id === null) {
                        $this->modelo->crear($datos);
                    } else {
                        $this->modelo->actualizar($id, $datos);
                    }
                    $_SESSION['mensaje'] = 'Registro guardado correctamente.';
                    \redirigir(\url($this->modulo));
                } catch (PDOException $error) {
                    if ($error->getCode() !== '23000') {
                        throw $error;
                    }
                    $errores[] = 'No se pudo guardar: dato duplicado o relación inválida.';
                }
            }
        }
        $this->vista('formulario', array_merge($this->datosVista(), compact('datos', 'errores', 'id')));
    }

    public function eliminar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Método no permitido.');
        }
        $id = $this->obtenerId();
        $this->buscarRegistro($id);
        if (method_exists($this->modelo, 'tieneAutos') && $this->modelo->tieneAutos($id)) {
            $this->vista('index', ['filas' => $this->modelo->listar(), 'errores' => ['No se puede eliminar: tiene autos relacionados.']]);
            return;
        }
        try {
            $this->modelo->eliminar($id);
            $_SESSION['mensaje'] = 'Registro eliminado correctamente.';
            \redirigir(\url($this->modulo));
        } catch (PDOException $error) {
            if ($error->getCode() !== '23000') {
                throw $error;
            }
            $this->vista('index', ['filas' => $this->modelo->listar(), 'errores' => ['No se puede eliminar: tiene autos relacionados.']]);
        }
    }
}
