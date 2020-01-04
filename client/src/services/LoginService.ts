export default class LoginService {
  static login(username: String, password: String) {
    const requestData = JSON.stringify({
      username,
      password
    });
    const requestOptions = {
      method: "POST",
      headers: {
        "Content-Type": "application/json", // header name contains a hyphen, so quotes are required
        Accept: "application/json"
      },
      body: requestData
    };

    return fetch(this.getBaseUrl() + "/api/login", requestOptions)
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

  private static getBaseUrl() {
    const loc = window.location;
    return loc.protocol + "//" + loc.host;
  }

  private static handleResponse(response: Response) {
    return response.text().then(text => {
      const content = text && JSON.parse(text);
      if (!response.ok) {
        if (response.status == 401) {
          LoginService.logout();
          location.reload();
        }
        const error = (content && content.message) || response.statusText;
        return Promise.reject(error);
      }
      return content.data;
    });
  }

  static logout() {
    localStorage.removeItem("jwt");
    localStorage.removeItem("expire");
    localStorage.removeItem("user");
  }
}
