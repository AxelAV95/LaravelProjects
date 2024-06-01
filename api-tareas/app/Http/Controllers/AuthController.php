<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth('api')->attempt($validator->validated())) {
            return response()->json([
                'status' => Response::HTTP_UNAUTHORIZED,
                'message' => 'User not authenticated'
            ]);
        }

        return $this->createNewToken($token);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    // public function logout() {
    //     auth('api')->logout();

    //     return response()->json(['message' => 'User successfully signed out']);
    // }

    public function logout(Request $request)
    {
        try {
            // Verificar si se ha enviado un token en el encabezado de autorización
            if ($request->hasHeader('Authorization')) {
                // Se ha enviado un token en el encabezado de autorización
                $token = $request->header('Authorization');

                // Aquí puedes continuar con la lógica para manejar el token JWT
                // Por ejemplo, puedes invalidar el token y desconectar al usuario
                JWTAuth::setToken($token)->invalidate();
                Auth::logout();

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => 'User successfully signed out'
                ]);
            } else {
                // No se ha enviado un token en el encabezado de autorización
                // Puedes devolver un mensaje de error indicando que el token no fue enviado
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Token not provided'
                ], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Token expired'
            ], Response::HTTP_FORBIDDEN);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'Invalid token'
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    //     public function logout()
    // {
    //     try {
    //         // Invalidar el token actual sin verificar si el usuario autenticado es el mismo que el usuario actual
    //         JWTAuth::parseToken()->invalidate();

    //         // Desconectar al usuario actual (si estás utilizando Auth::logout() en lugar de JWTAuth, puedes omitir esta línea)
    //         Auth::logout();

    //         return response()->json([
    //             'status' => Response::HTTP_OK,
    //             'message' => 'User successfully signed out'
    //         ]);
    //     } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
    //         return response()->json([
    //             'status' => Response::HTTP_FORBIDDEN,
    //             'message' => 'Token expired'
    //         ], Response::HTTP_FORBIDDEN);
    //     } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
    //         return response()->json([
    //             'status' => Response::HTTP_FORBIDDEN,
    //             'message' => 'Invalid token'
    //         ], Response::HTTP_FORBIDDEN);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
    //             'message' => 'Error'
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    // public function logout()
    // {
    //     try {
    //         // Verificar si el token es válido y obtener los detalles del usuario autenticado
    //         $user = JWTAuth::parseToken()->authenticate();

    //         // Verificar si el usuario autenticado es el mismo que el usuario actual
    //         if ($user->id !== auth('api')->user()->id) {
    //             return response()->json([
    //                 'status' => Response::HTTP_FORBIDDEN,
    //                 'message' => 'Invalid token'
    //             ], Response::HTTP_FORBIDDEN);
    //         }

    //         // Invalidar el token actual
    //         JWTAuth::parseToken()->invalidate();

    //         // Desconectar al usuario actual
    //         Auth::logout();

    //         return response()->json([
    //             'status' => Response::HTTP_OK,
    //             'message' => 'User successfully signed out'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
    //             'message' => 'Error'
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
    public function test()
    {
        return response()->json(['message' => 'User successfully signed out']);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }
}
