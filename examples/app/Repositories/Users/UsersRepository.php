<?php
namespace HZ\Laravel\Organizer\App\Repositories\Users;

use DB;
use Item;
use HZ\Laravel\Organizer\App\Models\User\User;
use HZ\Laravel\Organizer\App\Items\Users\Users;
use Illuminate\Http\Request;
use HZ\Laravel\Organizer\App\Managers\RepositoryManager;
use HZ\Laravel\Organizer\App\Contracts\RepositoryInterface;

class UsersRepository extends RepositoryManager implements RepositoryInterface
{
    /**
     * Model class name
     *
     * @var mixed
     */
    protected $model = User::class;

    /**
     * {@inheritDoc}
     */
    const TABLE = 'users';

    /**
     * {@inheritDoc}
     */
    const TABLE_ALIAS = 'u';

    /**
     * Set data for the given model
     *
     * @param \HZ\Laravel\Organizer\App\Models\User\User $user
     * @return void
     */
    protected function setData($user, $request)
    {
        $user->first_name = $request->first_name;

        $user->email = $request->email;

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    protected function filter()
    {
        if ($email = $this->option('email')) {
            $this->where('email', $email);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function select()
    {
        // select here using the select object
        // select object works only if there is a list of selects passed to list options
        if ($this->select->has('name')) {
            $this->select->replace('name', DB::raw('CONCAT(first_name, " ", last_name) as name'));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function orderBy()
    {
        // order by here
        list($orderBy, $direction) = $this->option('orderBy', ['id', 'DESC']);

        if ($orderBy == 'name') {
            $orderBy = 'first_name';
        }

        $this->query->orderBy($orderBy, $direction);
    }

    /**
     * Get a specific record
     * @param int id
     * @return fluent
     */
    public function get(int $id): Item
    {
        $user = $this->model::find($id);

        return new Users($user);
    }
}