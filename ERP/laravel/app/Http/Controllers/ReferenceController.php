<?php

namespace App\Http\Controllers;

use App\Models\Hr\Employee;
use App\Models\MetaReference;
use App\Models\MetaTransaction;
use Illuminate\Http\Request;

class ReferenceController extends Controller {
    /**
     * Get the next reference
     *
     * @param Request $request
     * @param MetaTransaction $transType
     * @return string
     */
    public function next(Request $request, MetaTransaction $transType)
    {
        return response()->json([
            'data' => MetaReference::getNext($transType->id, null, $request->input('context'))
        ]);
    }

    /**
     * Get the salary certificate reference
     *
     * @param Request $request
     * @return string
     */
    public function salaryCertificateRef(Request $request, Employee $employee)
    {
        $parts = array_filter([
            $employee->visa_company ? ($employee->visa_company->prefix ?: null) : null,
            'HR',
            'SC',
            $employee->emp_ref,
            $employee->currentSalary->id
        ]);

        return response()->json([
            'data' => implode('/', $parts)
        ]);
    }

    public function salaryTransferLetterRef(Request $request, Employee $employee)
    {
        $parts = array_filter([
            $employee->visa_company ? ($employee->visa_company->prefix ?: null) : null,
            'HR',
            'STL',
            $employee->emp_ref,
            $employee->currentSalary->id
        ]);

        return response()->json([
            'data' => implode('/', $parts)
        ]);
    }

}