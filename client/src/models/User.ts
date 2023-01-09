export default class User {
  fullName: string;
  new: boolean;

  constructor(data?: any) {
    if (data && data.given_name && data.name) {
      this.fullName = data.given_name + " " + data.name;
    } else {
      this.fullName = "";
    }
    this.new = !!(data && data.new);
  }
}
