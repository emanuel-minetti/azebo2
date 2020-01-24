import { LoginService } from "@/services/index";

export default class ApiService {
  protected static getBaseUrl() {
    const loc = window.location;
    return loc.protocol + "//" + loc.host + "/api/";
  }

  protected static getHeaders() {
    return {
      "Content-Type": "application/json", // header name contains a hyphen, so quotes are required
      Accept: "application/json",
      "Cache-Control": "no-cache"
    };
  }

  protected static getAuthHeader() {
    let jwt = localStorage.getItem("jwt");
    if (jwt) {
      jwt = jwt.substring(1, jwt.length - 1);
      return {
        Authorization: "Bearer " + jwt
      };
    } else {
      return {};
    }
  }

  protected static handleResponse(response: Response) {
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
      // reset jwt
      localStorage.setItem("jwt", JSON.stringify(content.data.jwt));
      localStorage.setItem("expire", JSON.stringify(content.data.expire));
      return content.data;
    });
  }
}
