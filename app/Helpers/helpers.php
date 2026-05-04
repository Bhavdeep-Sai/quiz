<?php

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
|
| Global helper functions for the application.
|
*/

if (!function_exists('route')) {
    function route($name, $parameters = [], $absolute = true)
    {
        return app('url')->route($name, $parameters, $absolute);
    }
}

if (!function_exists('asset')) {
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}

if (!function_exists('url')) {
    function url($path = null, $parameters = [], $secure = null)
    {
        if (is_null($path)) {
            return app('url');
        }

        return app('url')->to($path, $parameters, $secure);
    }
}

if (!function_exists('redirect')) {
    function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
        if (is_null($to)) {
            return app('redirect');
        }

        return app('redirect')->to($to, $status, $headers, $secure);
    }
}

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return app('path.storage') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('database_path')) {
    function database_path($path = '')
    {
        return app('path.database') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('resource_path')) {
    function resource_path($path = '')
    {
        return app('path.resources') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('public_path')) {
    function public_path($path = '')
    {
        return app('path.public') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return app('path.base') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('app_path')) {
    function app_path($path = '')
    {
        return app('path.app') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('abort')) {
    function abort($code, $message = '', $headers = [])
    {
        throw new \Symfony\Component\HttpKernel\Exception\HttpException($code, $message, null, $headers);
    }
}

if (!function_exists('abort_if')) {
    function abort_if($condition, $code, $message = '', $headers = [])
    {
        if ($condition) {
            abort($code, $message, $headers);
        }
    }
}

if (!function_exists('view')) {
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app('view');

        if (is_null($view)) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

if (!function_exists('response')) {
    function response($content = '', $status = 200, array $headers = [])
    {
        $factory = app('response');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($content, $status, $headers);
    }
}

if (!function_exists('now')) {
    function now($tz = null)
    {
        return \Illuminate\Support\Carbon::now($tz);
    }
}

if (!function_exists('collect')) {
    function collect($value = null)
    {
        return new \Illuminate\Support\Collection($value);
    }
}

if (!function_exists('dd')) {
    function dd(...$args)
    {
        foreach ($args as $arg) {
            var_dump($arg);
        }
        die(1);
    }
}
