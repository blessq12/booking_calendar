<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite('resources/css/app.css')
</head>

<body>
    <div class="flex items-center justify-center h-screen bg-gradient-to-br from-red-300 to-orange-600">
        <div class="bg-white/60 backdrop-blur-sm border border-black/10 rounded-md p-8 text-center">
            <div class="mb-8">
                <img src="/images/logo/logo.png" alt="logo" class="w-16 h-16 mx-auto ">
                <h2 class="text-2xl font-bold">Тепло на белой</h2>
            </div>
            <form action="{{ route('authorize') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-2">
                    <label for="email" class="text-xs font-medium text-gray-500 text-left">Email</label>
                    <input type="email" name="email" placeholder="Email"
                        class="border border-gray-300 bg-white rounded-md p-2">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="password" class="text-xs font-medium text-gray-500 text-left">Password</label>
                    <input type="password" name="password" placeholder="Password"
                        class="border border-gray-300 bg-white rounded-md p-2">
                </div>
                <button type="submit" class="bg-blue-500 text-white rounded-md p-2">Login</button>
            </form>
        </div>
</body>

</html>
