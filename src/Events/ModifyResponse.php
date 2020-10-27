<?php
namespace HZ\Illuminate\Mongez\Events;

class ModifyResponse
{
    /**
     * {@inheritDoc}
     */
    public function modifyResponse($response, $statusCode)
    {
        if ($statusCode == 200) {
            return [
                'data' => $response,
            ];
        } elseif (!empty($response['error'])) {
            $response['errors'] = [
                [
                    'key' => 'error',
                    'message' => $response['error'],
                ],
            ];

            unset($response['error']);
        } elseif (!empty($response['errors'])) {
            $errors = [];

            foreach ($response['errors'] as $key => $error) {
                $errors[] = [
                    'key' => $key,
                    'message' => $error,
                ];
            }

            $response['errors'] = $errors;
        }

        return $response;
    }
}
