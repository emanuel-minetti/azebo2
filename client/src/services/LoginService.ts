import ApiService from "@/services/ApiService";

export default class LoginService extends ApiService {
  static login(username: String, password: String) {
    const requestData = JSON.stringify({
      username,
      password
    });
    const requestOptions = {
      method: "POST",
      headers: this.getHeaders(),
      body: requestData
    };
    const url = this.getBaseUrl() + "login";

    return fetch(url, requestOptions)
      .then(this.handleResponse)
      .then(data => {
        if (data.jwt && data.expire) {
          localStorage.setItem("jwt", JSON.stringify(data.jwt));
          localStorage.setItem("expire", JSON.stringify(data.expire));
          localStorage.setItem("user", JSON.stringify(data.user));
        }
        return data;
      });
  }

  static logout() {
    localStorage.removeItem("jwt");
    localStorage.removeItem("expire");
    localStorage.removeItem("user");
  }
}
