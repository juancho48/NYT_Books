<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BooksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'author'    => 'string|max:255',
            'title'     => 'string|max:255',
            'isbn'      => 'array',
        ];
    }

    /**
     * Customize the offset validation messages.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            if (isset($data['offset']) && $this->isInvalidOffset($data['offset'])) {
                $validator->errors()->add('offset', 'Offset must be a number 0 or multiple of 20');
            }
            if (isset($data['isbn']) && $this->isInvalidIsbn($data['isbn'])) {
                $validator->errors()->add('isbn[]', 'ISBN must be a number 10 or 13 characters long');
            }
        });
    }

    /**
    * Get the error messages for the defined validation rules.*
    *
    * @param \Validator $validator
    * @return array
    */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'status' => 'failed'
        ], 422));
    }


    /**
     * Validation for offset.
     *
     * @param  Mixed  $validator
     * 
     * @return Boolean
     */
    private function isInvalidOffset($offset)
    {
        if (!is_numeric($offset)) {
            return true;
        }

        if ($offset == 0 || $offset % 20 == 0) {
            return false;
        }

        return true;
    }

    /**
     * Validation for ibsnArray.
     *
     * @param  Mixed  $validator
     * 
     * @return Boolean
     */
    private function isInvalidIsbn($ibsnArray)
    {
        if (!is_array($ibsnArray)) {
            return true;
        }

        foreach ($ibsnArray as $isbn) {
            if (strlen($isbn) != 10 && strlen($isbn) != 13) {
                return true;
            }
        }

        return false;
    }

}
