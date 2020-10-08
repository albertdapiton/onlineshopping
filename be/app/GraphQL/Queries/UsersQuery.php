<?php 

namespace App\GraphQL\Queries;

use App\Models\User;
use Auth;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

final class UsersQuery extends Query
{
    protected $attributes = [
        'name' => 'user'
    ];

    public function authorize($root, array $args, $ctx, ResolveInfo $resolveInfo = null, Closure $getSelectFields = null) : bool 
    {
        return ! Auth::guest();
    }

    public function getAuthorizationMessage(): string
    {
        return 'You are not authorized to perform this action';
    }

    public function type() : Type
    {
        return Type::listOf(GraphQL::type('User'));
    }

    public function args() : array
    {
        return [
            'roles' => [
                'name' => 'roles',
                'type' => Type::string(),
            ]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $users = User::orderBy('users.created_at', 'desc')->with('profile');
        
        if (isset($args['roles']) && ! is_null($args['roles'])) {
            $users = $users->whereHas('roles', function($query) use ($args) {
                $query->where('name', $args['roles']);
            });
        }
        
        return $users->get();
    }
}