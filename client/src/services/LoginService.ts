import { ApiService } from "@/services";

export default class LoginService extends ApiService {
  /**
   * Sends a login request to the API and handles the response.
   *
   *  Returns a `Promise<String>`.
   * @param username the username
   * @param password the password
   */
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

  /**
   * Removes all login data from local storage.
   */
  static logout() {
    localStorage.removeItem("jwt");
    localStorage.removeItem("expire");
    localStorage.removeItem("user");
  }
}
