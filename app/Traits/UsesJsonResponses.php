<?php

namespace App\Traits;

trait UsesJsonResponses
{
    /**
     * Success Response
     * @param $data
     * @param int $code
     * @return $this
     */
    public function successResponse($data, $code = \Illuminate\Http\Response::HTTP_OK)
    {
        return response()->json($data, $code);
    }
    public function errorResponse($message, $code)
    {
        return response()->json(['message' => $message, 'code' => $code], $code);
    }
    public function validate(\Illuminate\Http\Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException(
                $validator,
                $this->errorResponse(implode(',', $validator->messages()->all()), 422)
            );
        }

        //abort(422, implode(',', $validator->messages()->all()));
    }

    public function toJson($data, $code = \Illuminate\Http\Response::HTTP_OK)
    {
        return response($data, $code)->header('Content-Type', 'application/json');
    }
}
