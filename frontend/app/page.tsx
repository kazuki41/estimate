"use client";

import { useState, useEffect, useRef } from "react";
import axios from "../lib/axios"; // 💡 特注のwithXSRFToken入りAxios
import { useRouter } from "next/navigation";
import Link from "next/link";

// チャットメッセージの型定義
interface Message {
  id: number;
  sender: "user" | "ai";
  text: string;
}

// 見積もり項目の型定義（既存の構成を維持・categoryを追加）
interface EstimateItem {
  name: string;
  price: number;
  reason: string;
  category?: string;
}

// ユーザー情報の型定義
interface UserType {
  id: number;
  name: string;
  email: string;
}

export default function Home() {
  // --- 👤 認証・ユーザー状態 ---
  const [user, setUser] = useState<UserType | null>(null);
  const [authLoading, setAuthLoading] = useState(true);
  const router = useRouter();

  // --- 💬 チャット・見積もり状態 ---
  const [messages, setMessages] = useState<Message[]>([
    {
      id: 1,
      sender: "ai",
      text: "こんにちは！どのようなシステムをご希望ですか？実装したい機能や、追加・修正したい点を教えてください。",
    },
  ]);
  const [input, setInput] = useState("");
  const [results, setResults] = useState<EstimateItem[] | null>(null);
  const [total, setTotal] = useState<number>(0);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState("");

  const messagesEndRef = useRef<HTMLDivElement>(null);

  // 💡 新しく「保存された見積もりのID」を覚えるポケットを追加します
  const [savedEstimateId, setSavedEstimateId] = useState<number | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  // 💡 チャットが追加されたら自動で一番下までスクロールする心地よい処理
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [messages]);

  // 💡 起動時のログイン状態チェック（ロジックを完全維持）
  useEffect(() => {
    const checkAuth = async () => {
      try {
        await axios.get("/api/user").then((res) => {
          setUser(res.data);
        });
        setAuthLoading(false);
      } catch (err) {
        console.log("未ログイン、またはセッション切れです");
      }
    };
    checkAuth();
  }, [router]);

  // 🛠️ ログアウト処理（ロジックを完全維持）
  const handleLogout = async () => {
    try {
      await axios.get("/sanctum/csrf-cookie");
      await axios.post("/api/logout");
      setUser(null);
      router.push("/login");
    } catch (err) {
      console.error("ログアウトに失敗しました:", err);
    }
  };

  // 💡 どんな変な形（ネスト）で届いても、見積もり配列を自動で見つける関数（ロジックを完全維持）
  const findEstimateItems = (obj: any): any[] => {
    if (!obj) return [];
    if (Array.isArray(obj)) {
      if (obj.length === 0) return [];
      if (obj[0] && (obj[0].name !== undefined || obj[0].price !== undefined)) {
        return obj;
      }
      for (const item of obj) {
        const found = findEstimateItems(item);
        if (found.length > 0) return found;
      }
    }
    if (typeof obj === "object") {
      for (const key in obj) {
        const found = findEstimateItems(obj[key]);
        if (found.length > 0) return found;
      }
    }
    return [];
  };

  // 🚀 チャット送信＆見積もり生成・修正処理（旧 handleGenerate をチャット用に進化）
  const handleSend = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!input.trim() || isLoading) return;

    const userMessageText = input;
    setInput(""); // 入力欄をクリア
    setIsLoading(true);
    setError("");

    // 1. ユーザーのメッセージをタイムラインに即時追加
    setMessages((prev) => [
      ...prev,
      { id: Date.now(), sender: "user", text: userMessageText },
    ]);

    try {
      // 💡 今後バックエンド側でチャット履歴を考慮できるように、履歴(messages)も一緒に送る設計にします
      const response = await axios.post("/api/estimate", {
        userRequest: userMessageText,
        history: messages, // 過去のやり取りをバックエンドへ引き渡す
      });

      console.log("★APIから届いた生データ:", response.data);

      // 2. バックエンドの results を優先し、なければ自動掘り当て
      const rawItems = Array.isArray(response.data?.results)
        ? response.data.results
        : findEstimateItems(response.data);

      const items = rawItems.filter(
        (item: EstimateItem) =>
          item &&
          typeof item.name === "string" &&
          item.name.length > 0 &&
          item.price !== undefined &&
          item.price !== null,
      );

      if (items.length === 0) {
        throw new Error(
          response.data?.error ||
            response.data?.message ||
            "AIからのレスポンスから見積もり明細を正しく解析できませんでした。",
        );
      }

      // 3. 右側の見積もりプレビューを更新
      setResults(items);

      // 4. 合計金額の再計算
      const calculatedTotal = items.reduce(
        (sum: number, item: any) => sum + (Number(item.price) || 0),
        0,
      );
      setTotal(calculatedTotal);

      // 5. AIからのチャットのテキスト返答（もしバックエンドにmessageキーがなければ標準テキスト）
      const aiText =
        response.data.message ||
        "お待たせいたしました！ご要望を反映して、右側の概算見積書を更新しました。";
      setMessages((prev) => [
        ...prev,
        { id: Date.now(), sender: "ai", text: aiText },
      ]);
    } catch (err: any) {

      if (err?.response?.status === 429) {
        if (typeof window !== "undefined") {
          alert("⚠️ APIトークンの上限（1分間に3回）に達しました。しばらく時間を置いてから再度お試しください。");
        }
        setIsLoading(false);
        return; // ここで処理を終了させてチャットにエラー文を出さないようにする
      }
      
      const serverError =
        err?.response?.data?.error ||
        err?.response?.data?.message ||
        err?.message ||
        "見積もりの生成中にエラーが発生しました。";
      setError(serverError);
      setMessages((prev) => [
        ...prev,
        {
          id: Date.now(),
          sender: "ai",
          text: "申し訳ありません。見積もりの更新中にエラーが発生しました。",
        },
      ]);
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  // 💾 見積もり保存処理
  const handleSaveEstimate = async () => {
    if (!results || results.length === 0 || isSaving) return;
    setIsSaving(true);
    try {
      const response = await axios.post("/api/estimates", {
        items: results,
        total_amount: total,
      });

      // 保存に成功したら、Laravelから返ってきた見積もりIDをセット
      setSavedEstimateId(response.data.estimate_id);
      alert(
        "見積もりをデータベースに保存しました！「PDF出力」ボタンが押せるようになりました。",
      );
    } catch (err) {
      console.error("保存エラー:", err);
      alert("見積もりの保存に失敗しました。");
    } finally {
      setIsSaving(false);
    }
  };

  // 📄 PDFダウンロード処理（認証クッキーを安全に乗せて落とす方法）
  const handleDownloadPdf = async () => {
    if (!savedEstimateId) return;
    try {
      // blob（バイナリデータ）としてPDFを落としにいく
      const response = await axios.get(
        `/api/estimates/${savedEstimateId}/pdf`,
        {
          responseType: "blob",
        },
      );

      // ブラウザで自動ダウンロードさせるための魔法の処理
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", `estimate_${savedEstimateId}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (err) {
      console.error("PDFダウンロードエラー:", err);
      alert("PDFの生成に失敗しました。");
    }
  };

  // 💡 認証チェック中のガード画面
  if (authLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-gray-500 text-sm font-medium animate-pulse">
          認証情報を確認中...
        </div>
      </div>
    );
  }

  // 💡 未ログイン時のガード画面（ロジックを維持）
  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50 text-center py-8">
        <div>
          <h1 className="text-xl font-bold text-gray-700 mb-4">
            ログインしていません
          </h1>
          <Link
            href="/login"
            className="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 inline-block"
          >
            ログイン画面へ
          </Link>
        </div>
      </div>
    );
  }

  // =========================================================
  // ✨ 【ログイン成功時】LINE風チャット＆プレビューのメイン画面
  // =========================================================
  return (
    <div className="flex flex-col md:flex-row h-screen bg-gray-100 overflow-hidden">
      {/* 🎨 左半分：LINE風チャットタイムライン */}
      <div className="lg:h-full lg:w-1/2 flex flex-col h-full border-r border-gray-200 bg-[#7494c0]">
        {/* チャット上部ヘッダー（ユーザー名とログアウトを表示） */}
        <div className="bg-slate-800 text-white p-4 flex justify-between items-center shadow-sm z-10">
          <div>
            <div className="font-bold text-sm">AI見積もり相談チャット</div>
            <div className="text-xs text-slate-400">
              ログイン中: {user.name} さん
            </div>
          </div>
          <button
            onClick={handleLogout}
            className="bg-slate-700 border border-slate-600 hover:bg-slate-600 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors"
          >
            ログアウト
          </button>
        </div>

        {/* メッセージ表示タイムライン */}
        <div className="flex-1 overflow-y-auto p-4 space-y-4">
          {messages.map((msg) => (
            <div
              key={msg.id}
              className={`flex ${msg.sender === "user" ? "justify-end" : "justify-start"}`}
            >
              <div
                className={`max-w-[75%] rounded-2xl px-4 py-2 text-sm shadow-sm leading-relaxed ${
                  msg.sender === "user"
                    ? "bg-[#85e249] text-gray-900 rounded-tr-none" // ユーザー（緑）
                    : "bg-white text-gray-800 rounded-tl-none" // AI（白）
                }`}
              >
                {msg.text}
              </div>
            </div>
          ))}
          <div ref={messagesEndRef} />
        </div>

        {/* エラーメッセージ（チャット内に綺麗に表示） */}
        {error && (
          <div className="mx-4 my-2 bg-red-100 text-red-700 text-xs p-3 rounded-lg border border-red-200">
            ⚠️ {error}
          </div>
        )}

        {/* チャット入力送信エリア */}
        <form
          onSubmit={handleSend}
          className="p-4 bg-white border-t border-gray-200 flex gap-2"
        >
          <input
            type="text"
            value={input}
            onChange={(e) => setInput(e.target.value)}
            placeholder="例：会員登録機能を追加して / 金額を少し抑えて..."
            className="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            disabled={isLoading}
          />
          <button
            type="submit"
            className="bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors disabled:bg-gray-400"
            disabled={isLoading || !input.trim()}
          >
            {isLoading ? "思考中..." : "送信"}
          </button>
        </form>
      </div>

      {/* 📊 右半分：リアルタイム見積もりプレビュー */}
      <div className="lg:h-full lg:w-1/2 flex flex-col h-full bg-white p-6 overflow-y-auto">
        {/* 上部アクションバー */}
        <div className="flex justify-between items-center border-b border-gray-200 pb-4 mb-6">
          <div>
            <h2 className="text-xl font-bold text-gray-800">
              概算見積プレビュー
            </h2>
            <p className="text-xs text-gray-400 mt-0.5">
              チャットの要望に合わせてリアルタイムに更新されます
            </p>
          </div>

          {/* 今後実装する保存・PDFボタンの枠組み */}
          {/* 💡 ボタン部分を以下に差し替え */}
          <div className="flex gap-2">
            <button
              onClick={handleSaveEstimate}
              disabled={
                !results ||
                results.length === 0 ||
                isSaving ||
                savedEstimateId !== null
              }
              className="px-3 py-1.5 bg-gray-800 text-white rounded-lg text-xs font-medium hover:bg-gray-700 transition-colors disabled:bg-gray-200 disabled:text-gray-400"
            >
              {isSaving
                ? "保存中..."
                : savedEstimateId
                  ? "保存済み"
                  : "見積もりを保存"}
            </button>

            <button
              onClick={handleDownloadPdf}
              disabled={!savedEstimateId} // 💡 保存されるまで押せないようにガード
              className="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-medium hover:bg-red-700 transition-colors disabled:bg-gray-200 disabled:text-gray-400"
            >
              PDF出力
            </button>
          </div>
        </div>

        {/* 金額サマリーカード */}
        <div className="bg-slate-900 text-white rounded-2xl p-6 mb-6 shadow-md border border-slate-800">
          <p className="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">
            現在の合計金額（税別）
          </p>
          <p className="text-3xl font-black text-amber-400">
            ¥{total.toLocaleString()}{" "}
            <span className="text-xs font-normal text-slate-400 ml-1">~</span>
          </p>
        </div>

        {/* 明細リスト部分 */}
        <h3 className="font-bold text-gray-700 mb-3 text-sm tracking-wide">
          【お見積り内訳明細】
        </h3>

        {!results || results.length === 0 ? (
          <div className="flex-1 flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-xl p-8 text-center text-gray-400">
            <svg
              className="w-12 h-12 text-gray-300 mb-3"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            <p className="text-sm">
              左側のチャットに要望を入力して送信してください。
            </p>
            <p className="text-xs text-gray-400 mt-1">
              蓄積されたマスターデータを基にAIが自動計算します。
            </p>
          </div>
        ) : (
          <div className="space-y-3">
            {results.map((item, index) => (
              <div
                key={index}
                className="border border-gray-100 rounded-xl p-4 bg-gray-50 flex justify-between items-start hover:border-gray-200 transition-all shadow-sm"
              >
                <div className="space-y-1">
                  {item.category && (
                    <span className="text-[10px] bg-blue-50 text-blue-600 font-bold px-2 py-0.5 rounded-full inline-block">
                      {item.category}
                    </span>
                  )}
                  <h4 className="font-bold text-gray-800 text-sm">
                    {item.name}
                  </h4>
                  <p className="text-xs text-gray-500 leading-relaxed">
                    {item.reason}
                  </p>
                </div>
                <div className="font-bold text-gray-900 text-sm whitespace-nowrap ml-4 pt-1">
                  ¥{item.price?.toLocaleString() ?? "--"}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
