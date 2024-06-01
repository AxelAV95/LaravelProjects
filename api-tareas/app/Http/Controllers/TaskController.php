<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;

class TaskController extends Controller
{
    public function insertTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' =>  Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $validator->errors()
            ]);
        }

        try {
            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'user_id' => auth('api')->user()->id,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' =>  Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Error creating task'
            ]);
        }

        return response()->json([
            'status' =>  Response::HTTP_CREATED,
            'message' => 'Task registered successfully'
        ]);
    }

    public function updateTask(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);

            if ($task->user_id !== auth('api')->user()->id) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Unauthorized'
                ]);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => $validator->errors()
                ]);
            }

            $task->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Task not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Error updating task'
            ]);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => 'Task updated successfully'
        ]);
    }

    public function getAllTaskFromUser()
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => Response::HTTP_UNAUTHORIZED,
                'message' => 'User not authenticated'
            ]);
        }
        $tasks = $user->tasks()->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'tasks' => $tasks
        ]);
    }

    public function destroyTask($id)
    {
        
        try{
            $task = Task::findOrFail($id);

            if ($task->user_id !== auth('api')->user()->id) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Unauthorized'
                ], Response::HTTP_FORBIDDEN);
            }

            $task->delete();

        }catch(\Exception $e){
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Error deleting task'
            ]);

        }catch(ModelNotFoundException $e){
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Task not found'
            ]);
        }
        
        return response()->json([
            'status' => Response::HTTP_NO_CONTENT,
            'message' => 'Task deleted successfully'
        ]);
        
    }

    public function showTaskDetails($id)
    {
        try {
            $task = Task::findOrFail($id);

            if ($task->user_id !== auth('api')->user()->id) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Unauthorized'
                ]);
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'task' => $task
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Task not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Error retrieving task details'
            ]);
        }
    }
}
