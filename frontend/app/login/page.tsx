"use client";

import { useState } from "react";
import axios from "../../lib/axios";
import { useRouter } from "next/navigation";
import Link from "next/link";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const router = useRouter();

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");

    try {
      // 1. まず合鍵をもらう（これが 204 になるやつ）
      await axios.get("/sanctum/csrf-cookie");

      // 2. ログイン情報を送る
      // Laravel Breezeをインストールしていれば、このエンドポイントが有効です
      await axios.post("/login", {
        email: email,
        password: password,
      });

      // 3. 成功したら見積もりページ（トップ）へ
      router.push("/");
    } catch (err: any) {
      setError(
        "ログインに失敗しました。メールアドレスかパスワードが違います。",
      );
      console.error(err);
    }
  };

  return (
    <main className="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">
      <div className="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
        <h2 className="text-2xl font-bold text-center text-gray-800 mb-8">
          ユーザーログイン
        </h2>

        <form onSubmit={handleLogin} className="space-y-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              メールアドレス
            </label>
            <input
              type="email"
              required
              className="w-full p-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              パスワード
            </label>
            <input
              type="password"
              required
              className="w-full p-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition-all"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </div>

          {error && <p className="text-red-500 text-sm text-center">{error}</p>}

          <button
            type="submit"
            className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition-colors"
          >
            ログイン
          </button>
        </form>
      </div>

      <div className="text-center mt-4 space-y-2">
        <Link href="/register" className="text-sm text-blue-600 hover:underline block">
          アカウントをお持ちでない方はこちら（新規登録）
        </Link>
        <Link href="/admin" className="text-sm text-blue-600 hover:underline block">
          管理画面はこちら
        </Link>
      </div>

    </main>

  );
}
