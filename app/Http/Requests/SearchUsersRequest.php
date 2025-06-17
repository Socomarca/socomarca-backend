<?php
// app/Http/Requests/SearchUsersRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchUsersRequest extends FormRequest
{
    public function rules()
    {
        return [
            'roles' => 'sometimes|array',
            'roles.*' => 'string|in:admin,superadmin,supervisor,editor,cliente',
            'sort_field' => 'sometimes|string|in:id,name,email,created_at',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'name' => 'sometimes|string',
            'email' => 'sometimes|string|email',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}