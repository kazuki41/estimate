import axios from "axios";

const instance = axios.create({
  baseURL: "https://estimate.kohamart.com",
  withCredentials: true, // クッキーを一緒に送る設定
  withXSRFToken: true,
  headers: {
    "X-Requested-With": "XMLHttpRequest",
    "Accept": "application/json",
  },
});

// 💡 追記：すべてのAPI通信をリアルタイム監視する（インターセプター）
instance.interceptors.response.use(
  (response) => response, // 通信成功時はそのままデータをスルー
  (error) => {
    // もしサーバーから「401（ログインしてないよ）」が返ってきたら
    if (error.response && error.response.status === 401) {
      if (typeof window !== "undefined") {
        // どこにいても強制的にログイン画面へリダイレクト！
        window.location.href = "/login";
      }
    }
    return Promise.reject(error);
  }
);

export default instance;
