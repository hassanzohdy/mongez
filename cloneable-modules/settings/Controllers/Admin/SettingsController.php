<?php
namespace App\Modules\Settings\Controllers\Admin;

use HZ\Illuminate\Mongez\Managers\ApiController;
use Illuminate\Http\Request;

class SettingsController extends ApiController
{
    /**
     * {@inheritdoc}
     */
    protected $repository = 'settings';

    /**
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return $this->success([
            'records' => $this->repository->list($request->all()),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function update(Request $request)
    {        
        foreach (($request->settings ?: []) as $group => $groupData) {
            foreach ($groupData as $settingName => $settingInfo) {
                $this->repository->set([
                    'group' => $group,
                    'name' => $settingName,
                    'type' => $settingInfo['type'],
                    'value' => $settingInfo['value'],
                ]);
            }
        }

        return $this->index($request);
    }
}