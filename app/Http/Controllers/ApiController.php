<?php

namespace App\Http\Controllers;

use DB;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Employee;

use App\Models\Employee_worked_hours;

use function PHPUnit\Framework\isNull;

class ApiController extends Controller
{
    public function createEmployee(Request $request)
    {

        try {
            $jsonResult = array('success' => false, 'error' => [], 'data' => []);

            $validator = Validator::make($request->all(), [
                "gender_id"           =>    "required|integer",
                "job_id"        =>    "required|integer",
                "name"           =>    "required|string",
                "last_name"        =>    "required|string",
                "birthdate"           =>    "required|string",

            ], [
                'gender_id.required'    => 'campo gender requerido',
                'job_id.required'    => 'campo job requerido',
                'name.required'    => 'campo name requerido',
                'last_name.required'    => 'campo last_name requerido',
                'birthdate.required'    => 'campo birthdate requerido',
            ]);




            if ($validator->fails()) {
                $jsonResult['success'] = false;
                $jsonResult['error'] = $validator->errors();
            } else {
                $validator->after(function ($validator) use ($request, &$jsonResult) {
                    $validateName = DB::table('employees')->where(['name_e' => $request->name, 'last_name' => $request->last_name, 'activo' => 1])->exists();
                    if ($validateName) {
                        $validator->errors()->add('employees', 'Existe un registro con el mismo nombre y apellido del empleado, favor de elegir otro');
                    }

                    $edad_diff = date_diff(date_create(date('Y-m-d', strtotime($request->birthdate))), date_create(date("Y-m-d")));
                    $mayorEdad = $edad_diff->format('%y') > 18;
                    if (!$mayorEdad) {
                        $validator->errors()->add('employees', 'El empleado debe ser mayor de edad');
                    }

                    $validateJob = DB::table('jobs')->where(['id' => $request->job_id])->exists();
                    if (!$validateJob) {
                        $validator->errors()->add('employees', 'Cargo seleccionado incorrecto');
                    }

                    $validateGender = DB::table('genders')->where(['id' => $request->gender_id])->exists();
                    if (!$validateGender) {
                        $validator->errors()->add('employees', 'Genero seleccionado incorrecto');
                    }
                });


                if ($validator->fails()) {
                    $jsonResult['error'] = $validator->errors();
                } else {

                    $dataEmployee = array(
                        'name_e' => $request->name,
                        'last_name' => $request->last_name,
                        'birthdate' => $request->birthdate,
                        'gender_id' => $request->gender_id,
                        'job_id' => $request->job_id
                    );
                    $employee = null;
                    DB::transaction(function () use (&$dataEmployee, &$employee) {
                        $employee = Employee::create($dataEmployee);
                    });
                    $jsonResult['data'] = ['id' => $employee->id];
                    $jsonResult['success'] = true;
                }
            }
        } catch (Exception $e) {
            Log::error(__CLASS__ . '/' . __FUNCTION__ . ' (Linea: ' . $e->getLine() . '): ' . $e->getMessage());
            $jsonResult['error'] = array('Ocurrió un incidente crear el empleado, favor de intentar más tarde');
        }
        return response()->json($jsonResult);
    }



    public function addHoursEmployee(Request $request)
    {

        try {
            $jsonResult = array('success' => false, 'error' => [], 'data' => []);

            $validator = Validator::make($request->all(), [
                "employee_id"           =>    "required|integer",
                "worked_hours"        =>    "required|numeric",
                "worked_date"           =>    "required|date",


            ], [
                'employee_id.required'    => 'campo employee_id requerido',
                'worked_hours.required'    => 'campo worked_hours requerido',
                'worked_date.required'    => 'campo worked_date requerido'

            ]);


            if ($validator->fails()) {
                $jsonResult['success'] = false;
                $jsonResult['error'] = $validator->errors();
            } else {
                $validator->after(function ($validator) use ($request, &$jsonResult) {
                    $validateId = DB::table('employees')->where(['id' => $request->employee_id])->exists();
                    if (!$validateId) {
                        $validator->errors()->add('addHoursEmployee', 'Empleado incorrecto');
                    }

                    if ($request->worked_hours > 20) {
                        $validator->errors()->add('addHoursEmployee', 'Las hrs sobrepasan el limite');
                    }

                    if ($request->worked_date >= date("Y-m-d")) {
                        $validator->errors()->add('addHoursEmployee', 'La fecha de trabajo no corresponde al formato');
                    }

                    $validateRow = DB::table('employee_worked_hours')->where(['worked_date' => $request->worked_date])->exists();
                    if ($validateRow) {
                        $validator->errors()->add('addHoursEmployee', 'solo se permite un registro por dia');
                    }
                });


                if ($validator->fails()) {
                    $jsonResult['error'] = $validator->errors();
                } else {

                    $dataEmployee = array(
                        "employee_id" => $request->employee_id,
                        "worked_hours" => $request->worked_hours,
                        "worked_date" => $request->worked_date
                    );
                    $data = null;
                    DB::transaction(function () use (&$dataEmployee, &$data) {
                        $data = Employee_worked_hours::create($dataEmployee);
                    });
                    $jsonResult['data'] = ['id' => $data->id];
                    $jsonResult['success'] = true;
                }
            }
        } catch (Exception $e) {
            Log::error(__CLASS__ . '/' . __FUNCTION__ . ' (Linea: ' . $e->getLine() . '): ' . $e->getMessage());
            $jsonResult['error'] = array('Ocurrió un incidente crear el empleado, favor de intentar más tarde');
        }
        return response()->json($jsonResult);
    }


    public function listEmployeesByJobTitle(Request $request)
    {

        try {
            $jsonResult = array('success' => false, 'error' => [], 'data' => []);

            $validator = Validator::make($request->all(), [
                "job_id"           =>    "required|integer",
            ], [
                'job_id.required'    => 'campo job_id requerido',
            ]);

            if ($validator->fails()) {
                $jsonResult['error'] = $validator->errors();
            } else {
                $validateId = DB::table('jobs')->where(['id' => $request->job_id])->exists();
                if (!$validateId) {
                    $validator->errors()->add('listEmployeesByJobTitle', 'Puesto incorrecto');
                    $jsonResult['error'] = $validator->errors();
                } else {
                    $listEmployees = DB::table('employees')
                        ->select('id', 'name_e', 'last_name', 'birthdate', 'gender_id', 'job_id')
                        ->where(['job_id' => $request->job_id, 'activo' => 1])
                        ->get()->toArray();

                    foreach ($listEmployees as $item) {
                        $gender = DB::table('genders')->select('id', 'name_gender')->where(['id' => $item->gender_id])->first();
                        $gender = is_null($gender) ? ['id' => '', 'name' => ''] : $gender;
                        $item->gender = $gender;
                        unset($item->gender_id);

                        $job = DB::table('jobs')->select('id', 'name_job', 'salary')->where(['id' => $item->job_id])->first();
                        $job = is_null($job) ? ['id' => '', 'name' => '', 'salary' => ''] : $job;
                        $item->job = $job;
                        unset($item->job_id);
                    }

                    if (sizeof($listEmployees) > 0) {
                        $jsonResult['success'] = true;
                        $jsonResult['data'] = $listEmployees;
                    }
                }
            }
        } catch (Exception $e) {
            Log::error(__CLASS__ . '/' . __FUNCTION__ . ' (Linea: ' . $e->getLine() . '): ' . $e->getMessage());
            $jsonResult['error'] = array('Ocurrió un incidente al consultar los empleados, favor de intentar más tarde');
        }
        return response()->json($jsonResult);
    }



    public function getHoursEmployee(Request $request)
    {

        try {
            $jsonResult = array('success' => false, 'error' => [], 'data' => []);

            $validator = Validator::make($request->all(), [
                "employee_id"           =>    "required|integer",
                "start_date"           =>    "required|date",
                "end_date"           =>    "required|date",
            ], [
                'employee_id.required'    => 'campo employee_id requerido',
                'start_date.required'    => 'campo start_date requerido',
                'end_date.required'    => 'campo end_date requerido',
            ]);

            if ($validator->fails()) {
                $jsonResult['error'] = $validator->errors();
            } else {

                $validateId = DB::table('employees')->where(['id' => $request->employee_id])->exists();
                if (!$validateId) {
                    $validator->errors()->add('getHoursEmployee', 'Empleado incorrecto');
                    $jsonResult['error'] = $validator->errors();
                }

                if ($request->start_date >  $request->end_date) {
                    $validator->errors()->add('getHoursEmployee', 'Formato de fechas incorrecto');
                    $jsonResult['error'] = $validator->errors();
                } else {

                    $hours = DB::table('employee_worked_hours')
                        ->select('worked_hours')
                        ->where(['employee_id' => $request->employee_id])
                        ->whereBetween('worked_date', [$request->start_date, $request->end_date])
                        ->get()->toArray();
                    $sumHours = 0;
                    foreach ($hours as $item) {
                        $sumHours += $item->worked_hours;
                    }

                    if ($sumHours > 0) {
                        $jsonResult['success'] = true;
                    }
                    $jsonResult['data'] = ['total_worked_hours' => $sumHours];
                }
            }
        } catch (Exception $e) {
            Log::error(__CLASS__ . '/' . __FUNCTION__ . ' (Linea: ' . $e->getLine() . '): ' . $e->getMessage());
            $jsonResult['error'] = array('Ocurrió un incidente al consultar los empleados, favor de intentar más tarde');
        }
        return response()->json($jsonResult);
    }



    /**
     *
     *
     * 	Ejercicio 5. Realiza un web service que permita consultar cuanto se le pagó a un empleado en un rango de fechas.
	Se debe validar que el empleado exista y que la fecha de inicio sea menor a la fecha de fin.
Request:
{
    "employee_id": 1, // Id del empleado
    "start_date": “2019-01-01”, // Fecha de inicio
    "end_date": "2019-06-30", // Fecha de fin
}
Response:
{
    "payment": 100, // Cantidad pagada al empleado o null en caso de error
    "success": true // true si se logró obtener los datos, false en caso de error
}

     */

    public function getPayEmployee(Request $request)
    {

        try {
            $jsonResult = array('success' => false, 'error' => [], 'data' => []);

            $validator = Validator::make($request->all(), [
                "employee_id"           =>    "required|integer",
                "start_date"           =>    "required|date",
                "end_date"           =>    "required|date",
            ], [
                'employee_id.required'    => 'campo employee_id requerido',
                'start_date.required'    => 'campo start_date requerido',
                'end_date.required'    => 'campo end_date requerido',
            ]);

            if ($validator->fails()) {
                $jsonResult['error'] = $validator->errors();
            } else {

                $validateId = DB::table('employees')->where(['id' => $request->employee_id])->exists();
                if (!$validateId) {
                    $validator->errors()->add('getHoursEmployee', 'Empleado incorrecto');
                    $jsonResult['error'] = $validator->errors();
                }

                if ($request->start_date >  $request->end_date) {
                    $validator->errors()->add('getHoursEmployee', 'Formato de fechas incorrecto');
                    $jsonResult['error'] = $validator->errors();
                } else {

                    $payment = DB::table('employee_worked_hours')
                        ->select('worked_hours')
                        ->where(['employee_id' => $request->employee_id])
                        ->whereBetween('worked_date', [$request->start_date, $request->end_date])
                        ->get()->toArray();
                    $sumPayment = 0;
                    foreach ($payment as $item) {
                        $sumPayment += $item->worked_hours;
                    }

                    if ($sumPayment > 0) {
                        $employee = DB::table('employees as e')->join('jobs as j', 'e.job_id', '=', 'j.id')
                            ->select('salary')
                            ->where(['e.id' => $request->employee_id])
                            ->first();
                        if (is_null($employee)) {
                            throw new Exception('Salario no disponible', 100);
                        } else {
                            $jsonResult['success'] = true;
                            $paymentHr = ($employee->salary / 20) / 8;
                            $sumPayment = $sumPayment * $paymentHr;
                        }
                    }


                    $jsonResult['data'] = ['payment' => $sumPayment];
                }
            }
        } catch (Exception $e) {
            Log::error(__CLASS__ . '/' . __FUNCTION__ . ' (Linea: ' . $e->getLine() . '): ' . $e->getMessage());
            $jsonResult['error'] = array('Ocurrió un incidente al consultar los empleados, favor de intentar más tarde');
        }
        return response()->json($jsonResult);
    }
}
