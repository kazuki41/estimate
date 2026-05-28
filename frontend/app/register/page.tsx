"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import axios from "../../lib/axios";

export default function Register() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState(""); // 💡 Laravelはこれを確認用として要求します
  const [error, setError] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const router = useRouter();

  const handleRegister = async (
    e: React.FormEvent<HTMLFormElement> | React.MouseEvent<HTMLButtonElement>
  ) => {
    e.preventDefault();
    setError("");
    setIsLoading(true);

    if (password !== passwordConfirmation) {
      setError("パスワードが一致しません。");
      setIsLoading(false);
      return;
    }

    try {

      // console.log("今からCSRFを取りにいきます！");
      
      // debugger; // 🔥 【追加】ブラウザの時間をここで強制停止させる！

      // 1. ログインの時と同じく、まずは合鍵（CSRFクッキー）をもらう
      await axios.get("/sanctum/csrf-cookie");

      // 2. 新規登録APIにデータを投げる
      await axios.post("/register", {
        name: name,
        email: email,
        password: password,
        password_confirmation: passwordConfirmation, // 💡 スネークケースで送るのがLaravelのルールです
      });

      // 3. 成功したら自動的にログイン状態になるので、そのままトップ（見積もり画面）へ
      router.push("/");
    } catch (err: any) {
      if (err.response && err.response.data.errors) {
        // Laravelからバリデーションエラー（「すでに登録されたメールアドレスです」など）が返ってきた場合
        const serverErrors = err.response.data.errors;
        const firstError = Object.values(serverErrors)[0] as string[];
        setError(firstError[0]);
      } else {
        setError("アカウント作成に失敗しました。入力内容を確認してください。");
      }
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
            アカウントを作成
          </h2>
        </div>
        <form className="mt-8 space-y-4" onSubmit={handleRegister}>
          {error && (
            <div className="bg-red-50 text-red-600 p-3 rounded-lg text-sm">
              {error}
            </div>
          )}
          <div className="rounded-md shadow-sm space-y-3">
            <div>
              <label className="text-sm font-medium text-gray-600 block mb-1">お名前</label>
              <input
                type="text"
                required
                name="name"
                className="w-full p-3 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="山田 太郎"
                value={name}
                onChange={(e) => setName(e.target.value)}
              />
            </div>
            <div>
              <label className="text-sm font-medium text-gray-600 block mb-1">メールアドレス</label>
              <input
                type="email"
                required
                name="email"
                className="w-full p-3 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="example@email.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
              />
            </div>
            <div>
              <label className="text-sm font-medium text-gray-600 block mb-1">パスワード</label>
              <input
                type="password"
                required
                name="password"
                className="w-full p-3 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="8文字以上のパスワード"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
              />
            </div>
            <div>
              <label className="text-sm font-medium text-gray-600 block mb-1">パスワード（確認）</label>
              <input
                type="password"
                required
                name="password_confirmation"
                className="w-full p-3 border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="もう一度入力してください"
                value={passwordConfirmation}
                onChange={(e) => setPasswordConfirmation(e.target.value)}
              />
            </div>
          </div>

          <div>
            <button
              type="button"
              onClick={handleRegister}
              disabled={isLoading}
              className="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:bg-gray-400"
            >
              {isLoading ? "登録中..." : "アカウントを作成する"}
            </button>
          </div>

          <div className="text-center mt-4">
            <Link href="/login" className="text-sm text-blue-600 hover:underline">
              すでにアカウントをお持ちの方はこちら（ログイン）
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}