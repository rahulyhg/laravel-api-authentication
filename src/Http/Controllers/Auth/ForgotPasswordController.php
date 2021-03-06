<?php

namespace Elnooronline\LaravelApiAuthentication\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Elnooronline\LaravelApiAuthentication\ResetPassword;

class ForgotPasswordController extends Controller
{
    /**
     * @var
     */
    private $resetPassword;

    /**
     * ForgotPasswordController constructor.
     * @param $resetPassword $resetPassword
     */
    public function __construct(ResetPassword $resetPassword)
    {
        $this->resetPassword = $resetPassword;
    }

    /**
     * Send code to mobile.
     *
     * @param \Illuminate\Http\Request $request
     * @return null|array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendCode(Request $request)
    {
        $forgetPasswordRequest = config('api-authentication.validation.forget');

        $this->requestValidate($forgetPasswordRequest::createFromBase($request));

        $code = $this->resetPassword->createVerificationCode($request->email);

        // check if email verified
        if (! $code) {
            return response([
                'errors' => [
                    'code' => [trans('authentication::passwords.unverified-email')],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return [
            'links' => [
                'check_code' => [
                    'url' => route('api.verifyCode'),
                    'method' => 'POST'
                ]
            ],
        ];
    }

    /**
     * Check the code.
     *
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function verifyCode(Request $request)
    {
        $forgetPasswordRequest = config('api-authentication.validation.check-code');

        $this->requestValidate($forgetPasswordRequest::createFromBase($request));

        $token = $this->resetPassword->checkCode($request->email, $request->code);
        // No token has returned
        if (! $token) {
            return response([
                'errors' => [
                    'code' => [trans('authentication::passwords.wrong-code')],
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return [
            'token' => $token,
        ];
    }
}
