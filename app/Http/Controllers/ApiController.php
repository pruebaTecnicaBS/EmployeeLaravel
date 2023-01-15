<?php

namespace App\Http\Controllers;

use DB;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Employee;

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


            /* Se debe validar que el nombre y apellido del empleado no existan, que el empleado sea mayor de edad y que el género y puesto existan en sus tablas correspondientes.*/

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

    /*
    	Ejercicio 2. Realiza un web service que permita agregar horas trabajadas de un empleado (ver anexo 1.2).
	Se debe validar que el empleado exista, que el total de horas trabajadas no sea mayor a 20 horas y que la fecha
    de trabajo sea menor o igual a la actual y no se duplique por empleado (un empleado sólo puede tener un registro de horas trabajadas por día).

Request
    {
    "employee_id": 1, // Id del empleado
    "worked_hours": 10, // Horas trabajadas
    "worked_date": "2019-01-01" // Fecha que trabajó el empleado
}


Response:
{
    "id": 100, // Id insertado o null en caso de error.
    "success": true // true si se insertó el registro o false en caso de error
}




    */

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


            /* Se debe validar que el nombre y apellido del empleado no existan, que el empleado sea mayor de edad y que el género y puesto existan en sus tablas correspondientes.*/

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
}
